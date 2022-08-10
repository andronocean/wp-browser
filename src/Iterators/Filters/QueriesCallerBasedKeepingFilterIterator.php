<?php
/**
 * A filter that will keep queries depending on the caller that originated them.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

/**
 * Class QueriesCallerBasedKeepingFilterIterator
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
class QueriesCallerBasedKeepingFilterIterator extends \FilterIterator
{

    /**
     * The list of elements to look for.
     *
     * @var array<string>
     */
    protected $needles = [];

    /**
     * Check whether the current element of the iterator is acceptable.
     *
     * @link  http://php.net/manual/en/filteriterator.accept.php
     *
     * @return bool True if the current element is acceptable, otherwise false.
     */
    #[\ReturnTypeWillChange]
    public function accept(): bool
    {
        $query = $this->getInnerIterator()->current();
        foreach ($this->needles as $needle) {
            if (preg_match("/(?<!\\(')" . preg_quote($needle) . "(?!'\\))/", $query[2])) {
                return true;
            }
        }

        return false;
    }
}
