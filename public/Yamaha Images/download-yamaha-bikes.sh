#!/usr/bin/env bash
# Requires Bash, curl and Python 3; no pip packages needed.
# Usage: bash download-yamaha-bikes.sh [--output DIRECTORY] [--html SAVED_PAGE]
# Preview URLs without downloading: bash download-yamaha-bikes.sh --list
set -euo pipefail
command -v curl >/dev/null || { echo 'curl is required.' >&2; exit 1; }
command -v python3 >/dev/null || { echo 'Python 3 is required.' >&2; exit 1; }

python3 - "$@" <<'PY'
import argparse
import hashlib
import re
import subprocess
import sys
import tempfile
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import quote, unquote, urljoin, urlsplit, urlunsplit

PAGE = 'https://www.yamaha-motor.com.au/land/road-motorcycle/supersport'
MODEL = re.compile(r'yzf[\s_-]*r\d', re.I)
VOID = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr'}

class Node:
    def __init__(self, tag='', attrs=(), parent=None):
        self.tag, self.attrs, self.parent = tag, dict(attrs), parent
        self.children, self.links, self.images = [], False, 0

class Parser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.root = self.current = Node()
        self.nodes = []

    def handle_starttag(self, tag, attrs):
        node = Node(tag, attrs, self.current)
        self.current.children.append(node)
        self.nodes.append(node)
        if tag not in VOID:
            self.current = node

    def handle_startendtag(self, tag, attrs):
        parent = self.current
        self.handle_starttag(tag, attrs)
        self.current = parent

    def handle_endtag(self, tag):
        node = self.current
        while node.parent is not None:
            if node.tag == tag:
                self.current = node.parent
                return
            node = node.parent

def image_links(node):
    candidates = []
    for key in ('src', 'data-src', 'data-lazy-src', 'data-original'):
        value = node.attrs.get(key)
        if value:
            candidates.append((0, value))
    for key in ('srcset', 'data-srcset'):
        for item in (node.attrs.get(key) or '').split(','):
            parts = item.strip().split()
            if parts:
                score = 0
                if len(parts) > 1:
                    try:
                        score = float(parts[1][:-1])
                    except ValueError:
                        pass
                candidates.append((score, parts[0]))
    return candidates

def normalize(value):
    parts = urlsplit(urljoin(PAGE, value))
    if parts.scheme not in ('http', 'https') or parts.username or parts.password:
        return None
    if parts.path.lower().endswith('.svg'):
        return None
    return urlunsplit((parts.scheme, parts.netloc,
                      quote(parts.path, safe='/%:@-._~!$&\'()*+,;='),
                      quote(parts.query, safe='%=&+/:;,@-._~!$\'()*'), ''))

def curl(url, destination):
    result = subprocess.run([
        'curl', '--fail', '--location', '--silent', '--show-error',
        '--compressed', '--retry', '2', '--connect-timeout', '20',
        '--max-time', '120', '--user-agent', 'Mozilla/5.0',
        '--referer', PAGE, '--output', str(destination), '--', url
    ])
    if result.returncode:
        raise RuntimeError('curl failed (exit %s).' % result.returncode)

def extension(path):
    with path.open('rb') as handle:
        head = handle.read(64)
    if head.startswith(b'\x89PNG\r\n\x1a\n'):
        return '.png'
    if head.startswith(b'\xff\xd8\xff'):
        return '.jpg'
    if head[:6] in (b'GIF87a', b'GIF89a'):
        return '.gif'
    if head.startswith(b'RIFF') and head[8:12] == b'WEBP':
        return '.webp'
    if b'ftypavif' in head or b'ftypavis' in head:
        return '.avif'
    raise RuntimeError('Response is not a supported image; it may be a security check.')

def main():
    ap = argparse.ArgumentParser(description='Download YZF bike images referenced by Yamaha Supersport HTML.')
    ap.add_argument('--html', type=Path, help='Use a browser-saved HTML page instead of fetching the page.')
    ap.add_argument('--output', type=Path, default=Path('yamaha-supersport-images'))
    ap.add_argument('--list', action='store_true', help='Print extracted URLs without downloading images.')
    args = ap.parse_args()
    with tempfile.TemporaryDirectory(prefix='yamaha-images-') as temporary:
        temporary = Path(temporary)
        page_file = args.html or temporary / 'page.html'
        if not args.html:
            curl(PAGE, page_file)
        html = page_file.read_text(encoding='utf-8', errors='replace')
        parser = Parser()
        parser.feed(html)
        for node in reversed(parser.nodes):
            node.links = bool(MODEL.search(node.attrs.get('href') or '')) or any(c.links for c in node.children)
            node.images = (node.tag == 'img') + sum(c.images for c in node.children)
        urls = []
        for node in parser.nodes:
            if node.tag != 'img':
                continue
            candidates = image_links(node)
            if node.parent and node.parent.tag == 'picture':
                for child in node.parent.children:
                    if child.tag == 'source':
                        candidates.extend(image_links(child))
            bike = any(MODEL.search(value or '') for value in node.attrs.values())
            ancestor = node.parent
            while ancestor and not bike:
                bike = ancestor.links and 0 < ancestor.images <= 4
                ancestor = ancestor.parent
            candidates = [(score, url) for score, value in candidates
                          if (url := normalize(value)) and (bike or MODEL.search(unquote(url)))]
            if candidates:
                urls.append(max(candidates, key=lambda item: item[0])[1])
        urls = list(dict.fromkeys(urls))
        if not urls:
            if '_Incapsula_Resource' in html:
                raise RuntimeError('Yamaha returned a JavaScript security check. Open the page in your browser, '
                                   'save it as Webpage, HTML Only, and rerun with --html supersport.html.')
            raise RuntimeError('No YZF image links found. If bikes load with JavaScript, save the fully loaded '
                               'page in your browser and rerun with --html supersport.html.')
        if args.list:
            print('\n'.join(urls))
            return
        args.output.mkdir(parents=True, exist_ok=True)
        (args.output / 'image-urls.txt').write_text('\n'.join(urls) + '\n', encoding='utf-8')
        failed = 0
        for index, url in enumerate(urls, 1):
            name = unquote(urlsplit(url).path.rsplit('/', 1)[-1])
            stem = re.sub(r'[^A-Za-z0-9._-]+', '_', Path(name).stem).strip('._-')[:140] or 'bike'
            suffix = hashlib.sha256(url.encode()).hexdigest()[:10]
            download = temporary / 'download'
            try:
                curl(url, download)
                target = args.output / (stem + '-' + suffix + extension(download))
                download.replace(target)
                print('[%s/%s] %s' % (index, len(urls), target), flush=True)
            except RuntimeError as error:
                failed += 1
                print('[%s/%s] Failed: %s (%s)' % (index, len(urls), url, error), file=sys.stderr)
        print('Saved %s image(s) to %s.' % (len(urls) - failed, args.output.resolve()))
        if failed:
            sys.exit(1)

try:
    main()
except (OSError, RuntimeError, ValueError) as error:
    sys.exit(str(error))
PY
