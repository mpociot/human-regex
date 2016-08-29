<?php

namespace Mpociot\HumanRegex;

use Closure;

class HumanRegex
{
    /** @var string */
    protected $modifiers = "m";

    /** @var bool */
    protected $globalMatch = false;

    /** @var string */
    protected $method = "preg_match";

    /** @var string */
    protected $prefixes = "";

    /** @var string */
    protected $suffixes = "";

    /** @var array */
    protected $expressionParts = [];

    /** @var string */
    protected $lastValue;

    /**
     * @return HumanRegex
     */
    public static function create() : HumanRegex
    {
        return new static;
    }

    /**
     * Add a part to the expression
     *
     * @param  string $value
     * @return HumanRegex
     */
    public function add($value) : HumanRegex
    {
        $this->checkParenthesis($value);
        $this->expressionParts[] = $this->lastValue = $value;

        return $this;
    }

    /**
     * If the last expression has an unclosed
     * parenthesis, we need to close it now.
     *
     * @param string $value
     */
    protected function checkParenthesis($value = '')
    {
        if (strpos($this->lastValue, ')|(?:') === 0 && (strpos($value, '(') === 0 || $value === '')) {
            $this->add('))');
        }
    }

    /**
     * Find a value inside the expression.
     * Alias for then()
     *
     * @param string $value
     * @return HumanRegex
     */
    public function find($value) : HumanRegex
    {
        return $this->then($value);
    }

    /**
     * Active global matches.
     *
     * @return $this
     */
    public function global()
    {
        $this->globalMatch = true;
        $this->method = 'preg_match_all';

        return $this;
    }

    /**
     * Find a value inside the expression.
     *
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function then($value) : HumanRegex
    {
        return $this->add('(?:' . $this->getValue($value) . ')');
    }

    /**
     * Matches the start of a string without consuming any characters.
     *
     * @return HumanRegex
     */
    public function startOfString() : HumanRegex
    {
        $this->prefixes = '^';

        return $this;
    }

    /**
     * Matches the start of a string without consuming any characters.
     *
     * @return HumanRegex
     */
    public function endOfString() : HumanRegex
    {
        $this->suffixes = '$';

        return $this;
    }

    /**
     * Matches anything.
     * @return HumanRegex
     */
    public function anything() : HumanRegex
    {
        return $this->add('(?:.*)');
    }

    /**
     * Matches anything except for the given value.
     *
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function anythingBut($value) : HumanRegex
    {
        if (is_string($value) && strlen($value) === 1) {
            return $this->add('[^'.$this->getValue($value).']')->atLeast(1);
        }

        return $this->notAhead($this->getValue($value))->once()->anything();
    }

    /**
     * Alias for either()
     *
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function findEither($value) : HumanRegex
    {
        return $this->either($value);
    }

    /**
     * Alias for either()
     *
     * @param string $value
     * @return HumanRegex
     */
    public function thenEither($value) : HumanRegex
    {
        return $this->either($value);
    }

    /**
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function either($value) : HumanRegex
    {
        return $this->add('(?:(?:' . $this->getValue($value));
    }

    /**
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function or($value) : HumanRegex
    {
        $expression = ')';
        if (substr($this->lastValue, -1) === ')') {
            $expression = '';
        }
        return $this->add($expression . '|(?:' . $this->getValue($value));
    }

    /**
     * Match the previous expression multiple times.
     *
     * @return HumanRegex
     */
    public function multipleTimes() : HumanRegex
    {
        $this->checkParenthesis();
        return $this->add('+');
    }

    /**
     * Limit the previous expression to one occurrence.
     * @return HumanRegex
     */
    public function once() : HumanRegex
    {
        return $this->limit(1);
    }

    /**
     * Limit the previous expression to at least X number of occurrences.
     *
     * @param int $times
     * @return HumanRegex
     */
    public function atLeast(int $times) : HumanRegex
    {
        return $this->limit($times, -1);
    }

    /**
     * Limit the previous expression to exactly X number of occurrences.
     *
     * @param int $times
     * @return HumanRegex
     */
    public function exactly(int $times) : HumanRegex
    {
        return $this->limit($times);
    }

    /**
     * Limit the occurrence of the previous expression.
     *
     * @param int $min
     * @param int $max
     * @return HumanRegex
     */
    public function limit(int $min, int $max = 0) : HumanRegex
    {
        if (substr($this->lastValue, -1) === '+') {
            array_pop($this->expressionParts);
        }

        if ($max === 0) {
            $value = '{' . $min . '}';
        } elseif ($max < $min) {
            $value = '{' . $min . ',}';
        } else {
            $value = '{' . $min . ',' . $max . '}';
        }

        return $this->add($value);
    }

    /**
     * Match the previous expression optionally.
     * @return HumanRegex
     */
    public function optional() : HumanRegex
    {
        return $this->maybe();
    }

    /**
     * Match single digit.
     *
     * @return HumanRegex
     */
    public function digit() : HumanRegex
    {
        return $this->add('(?:\\d)');
    }

    /**
     * Match multiple digits.
     *
     * @return HumanRegex
     */
    public function digits() : HumanRegex
    {
        return $this->digit()->multipleTimes();
    }

    /**
     * Match single letter.
     *
     * @return HumanRegex
     */
    public function letter() : HumanRegex
    {
        return $this->add('(?:[a-zA-Z])');
    }

    /**
     * Match multiple letters.
     *
     * @return HumanRegex
     */
    public function letters() : HumanRegex
    {
        return $this->letter()->multipleTimes();
    }

    /**
     * Matches one whitespace character.
     *
     * @return HumanRegex
     */
    public function whitespace() : HumanRegex
    {
        return $this->add('(?:\\s)');
    }

    /**
     * Matches one or more whitespace characters.
     *
     * @return HumanRegex
     */
    public function whitespaces() : HumanRegex
    {
        return $this->whitespace()->multipleTimes();
    }

    /**
     * Match single alphanumeric character.
     *
     * @return HumanRegex
     */
    public function alphanumeric() : HumanRegex
    {
        return $this->add('(?:[a-zA-Z0-9])');
    }

    /**
     * Match multiple alphanumeric characters.
     *
     * @return HumanRegex
     */
    public function alphanumerics() : HumanRegex
    {
        return $this->alphanumeric()->multipleTimes();
    }

    /**
     * Match a line break.
     *
     * @return HumanRegex
     */
    public function lineBreak() : HumanRegex
    {
        return $this->add('(?:\\n|(\\r\\n))');
    }

    /**
     * Shorthand for lineBreak.
     *
     * @return HumanRegex
     */
    public function br() : HumanRegex
    {
        return $this->lineBreak();
    }

    /**
     * Negates the previous expression.
     *
     * @param Closure $value
     * @return HumanRegex
     */
    public function not(Closure $value) : HumanRegex
    {
        return $this->notAhead($value);
    }

    /**
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function notAhead($value) : HumanRegex
    {
        return $this->add("(?!".$this->getValue($value).")");
    }

    /**
     * Matches any of the given values.
     *
     * @param array $values
     * @return HumanRegex
     */
    public function anyOf(array $values) : HumanRegex
    {
        $firstValue = array_shift($values);
        $this->findEither($firstValue);

        foreach ($values as $value) {
            $this->or($value);
        }

        return $this;
    }

    /**
     * @param string|Closure $value
     * @return HumanRegex
     */
    public function maybe($value = null) : HumanRegex
    {
        if ($value instanceof Closure || !is_null($value)) {
            return $this->add('(?:' . $this->getValue($value) . ')?');
        } else {
            return $this->add('?');
        }
    }

    /**
     * Creates a new capture group.
     *
     * @param Closure $closure
     * @return HumanRegex
     */
    public function capture(Closure $closure) : HumanRegex
    {
        $expression = $closure(new HumanRegex())->getRaw();

        $this->add('(' . $expression . ')');

        return $this;
    }

    /**
     * @param $string string
     * @return string
     */
    private function sanitize($string) : string
    {
        return preg_quote($string, "/");
    }

    /**
     * @param $value
     * @return bool
     */
    public function matches($value) : bool
    {
        return (bool)preg_match($this->getRegex(), $value);
    }

    /**
     * @param $haystack
     * @return array
     */
    public function findMatches($haystack) : array
    {
        $matches = [];

        call_user_func_array($this->method, [$this->getRegex(), $haystack, &$matches]);

        /**
         * When calling preg_match_all, we need to return the first index
         * of the matches array.
         */
        if (!isset($matches[1]) && isset($matches[0]) && is_array($matches[0])) {
            return $matches[0];
        }

        return $matches;
    }

    /**
     * Replace a regular expression matches on
     * a text with the given closure result.
     *
     * @param string $text
     * @param Closure $callback
     * @return string
     */
    public function replace(string $text, Closure $callback) : string
    {
        return preg_replace_callback($this->getRegex(), function ($matches) use ($callback) {
            return call_user_func_array($callback, $matches);
        },  $text);
    }

    /**
     * @param $value string|Closure
     * @return string
     */
    protected function getValue($value) : string
    {
        if ($value instanceof Closure) {
            return $value(new HumanRegex())->getRaw();
        } elseif ($value instanceof HumanRegex) {
            return $value->getRaw();
        }

        return $this->sanitize($value);
    }

    /**
     * @return string
     */
    public function getRegex() : string
    {
        return '/' . $this->prefixes . $this->getRaw() . $this->suffixes . '/' . $this->modifiers;
    }

    /**
     * @return string
     */
    public function getRaw() : string
    {
        $this->checkParenthesis();
        return implode("", $this->expressionParts);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getRegex();
    }
}
