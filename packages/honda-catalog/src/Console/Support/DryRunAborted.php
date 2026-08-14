<?php

namespace Honda\Catalog\Console\Support;

use RuntimeException;

/**
 * Thrown at the end of a --dry-run sync to unwind the wrapping DB
 * transaction (rolling back everything the run touched) without treating
 * the run itself as a failure.
 */
class DryRunAborted extends RuntimeException {}
