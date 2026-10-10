@props(['title' => 'Enquire Now'])

<div
    x-data="{
        open: false,
        subject: '',
        sending: false,
        sent: false,
        errors: {},
        name: '',
        email: '',
        phone: '',
        message: '',
        reset() {
            this.sending = false;
            this.sent = false;
            this.errors = {};
            this.name = '';
            this.email = '';
            this.phone = '';
            this.message = '';
        },
        async submit() {
            this.sending = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route('yamaha.enquire') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '',
                    },
                    body: JSON.stringify({
                        subject: this.subject,
                        name: this.name,
                        email: this.email,
                        phone: this.phone,
                        message: this.message,
                        page_url: window.location.href,
                    }),
                });

                const data = await response.json();

                if (response.status === 422) {
                    this.errors = Object.fromEntries(
                        Object.entries(data.errors ?? {}).map(([k, v]) => [k, v[0]])
                    );
                    return;
                }

                if (!response.ok) {
                    this.errors = { general: data.message ?? 'Something went wrong. Please try again.' };
                    return;
                }

                this.sent = true;
            } catch (e) {
                this.errors = { general: 'Something went wrong. Please try again.' };
            } finally {
                this.sending = false;
            }
        },
    }"
    x-on:open-enquiry.window="open = true; subject = $event.detail.subject ?? ''; reset();"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    style="display:none;"
>
    <div class="absolute inset-0 bg-black/60" x-on:click="open = false"></div>

    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6" x-on:click.outside="open = false">
        <button type="button" x-on:click="open = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <template x-if="!sent">
            <div>
                <h3 class="font-black text-lg uppercase text-gray-900 mb-1">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mb-5" x-show="subject" x-text="subject"></p>

                <form x-on:submit.prevent="submit()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Name</label>
                        <input type="text" x-model="name" required maxlength="100"
                               class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm">
                        <p class="text-xs text-red-500 mt-1" x-show="errors.name" x-text="errors.name"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Email</label>
                        <input type="email" x-model="email" required maxlength="150"
                               class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm">
                        <p class="text-xs text-red-500 mt-1" x-show="errors.email" x-text="errors.email"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Phone <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                        <input type="tel" x-model="phone" maxlength="30"
                               class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm">
                        <p class="text-xs text-red-500 mt-1" x-show="errors.phone" x-text="errors.phone"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Message <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                        <textarea x-model="message" rows="3" maxlength="1500"
                                  class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm"></textarea>
                        <p class="text-xs text-red-500 mt-1" x-show="errors.message" x-text="errors.message"></p>
                    </div>

                    <p class="text-xs text-red-500" x-show="errors.general" x-text="errors.general"></p>

                    <button type="submit" :disabled="sending"
                            class="w-full text-center font-black py-3 px-6 rounded-lg transition text-white bg-brand hover:bg-brand-dark disabled:opacity-60">
                        <span x-show="!sending">Send Enquiry</span>
                        <span x-show="sending" x-cloak>Sending…</span>
                    </button>
                </form>
            </div>
        </template>

        <template x-if="sent">
            <div class="py-6 text-center">
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="font-black text-lg uppercase text-gray-900 mb-1">Thanks, <span x-text="name"></span>!</h3>
                <p class="text-sm text-gray-500">We've received your enquiry and will be in touch shortly.</p>
                <button type="button" x-on:click="open = false"
                        class="mt-5 font-black text-sm uppercase tracking-widest text-brand hover:text-brand-dark">
                    Close
                </button>
            </div>
        </template>
    </div>
</div>
