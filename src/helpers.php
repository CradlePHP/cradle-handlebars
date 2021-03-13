<?php //-->

use Cradle\Data\Registry;
use Cradle\I18n\Timezone;

$this('handlebars')
  /* String Helpers
  ----------------------------------------------------------------------------*/

  /**
   * Capitalizes string
   *
   * @param *string
   * @param number  if 1, then only the first word
   *
   * @return string
   */
  ->registerHelper('capital', function ($string, $options) {
    if ($options == 1) {
      return ucfirst($string);
    }

    return ucwords($string);
  })

  /**
   * Limits the string to specified character count
   *
   * @param *string
   * @param int   The character limit
   *
   * @return string
   */
  ->registerHelper('chars', function ($value, $length) {
    return substr($value, 0, $length);
  })

  /**
   * Strips HTML tags and then limits the
   * string to specified word count to make an excerpt
   *
   * @param *string HTML string
   * @param string  Allowable tags
   *
   * @return string
   */
  ->registerHelper('excerpt', function ($html, $length, $options) {
    $allowable = '<p><br>';
    if (is_string($options)) {
      $allowable = $options;
    }

    $value = strip_tags($html, $allowable);

    if (str_word_count($value, 0) > $length) {
      $words = str_word_count($value, 2);
      $position = array_keys($words);
      $value = substr($value, 0, $position[$length]);
    }

    return $value;
  })

  /**
   * Lower cases string
   *
   * @param *string
   *
   * @return string
   */
  ->registerHelper('lower', function ($value) {
    return strtolower($value);
  })

  /**
   * Transforms markdown to HTML
   *
   * @param *string $markdown Markdoen string
   *
   * @return string
   */
  ->registerHelper('markdown', function ($markdown, $options) {
    $parsedown = new Parsedown;
    return $parsedown->text($markdown);
  })

  /**
   * Strips HTML tags
   *
   * @param *string HTML string
   * @param string  Allowable tags
   *
   * @return string
   */
  ->registerHelper('strip', function ($html, $options) {
    $allowable = '<p><b><em><i><strong><b><br><u><ul><li><ol>';
    if (is_string($options)) {
      $allowable = $options;
    }

    return strip_tags($html, $allowable);
  })

  /**
   * Upper cases string
   *
   * @param *string
   *
   * @return string
   */
  ->registerHelper('upper', function ($value) {
    return strtoupper($value);
  })

  /**
   * Limits the string to specified word count
   *
   * @param *string
   * @param int   The word limit
   *
   * @return string
   */
  ->registerHelper('words', function ($value, $length) {
    if (str_word_count($value, 0) > $length) {
      $words = str_word_count($value, 2);
      $position = array_keys($words);
      $value = substr($value, 0, $position[$length]);
    }

    return $value;
  })

  /* Number Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Formats numbers to price format
   *
   * @param *number
   *
   * @return string
   */
  ->registerHelper('between', function ($number, $lower, $upper, $options) {
    if ($lower <= $number && $number <= $upper) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * Parses Currency
   *
   * @param *string $abbr
   *
   * @return string
   */
  ->registerHelper('currency', function ($abbr, $options) {
    static $currencies = null;
    if (!$currencies) {
      $currencies = file_get_contents(__DIR__ . '/assets/currencies.json');
      $currencies = json_decode($currencies, true);
    }

    if (!isset($currencies[$abbr]['symbol_native'])) {
      return $abbr;
    }

    return $currencies[$abbr]['symbol_native'];
  })

  /**
   * Computes a math formula
   *
   * @param *string formula template
   * @param *array  formula variables
   *
   * @return string
   */
  ->registerHelper('formula', function ($template, $variables = [], $options) {
    $compiler = cradle('handlebars')->getHelper('compile');
    $price = cradle('handlebars')->getHelper('price');
    $formula = $compiler($template, $variables);

    if (preg_match('/[a-zA-Z]/', $formula)) {
      return cradle('lang')->translate('Invalid Formula');
    }

    $expression = sprintf('return %s ;', $formula);

    try {
      $value = @eval($expression);
    } catch (Throwable $e) {
      return 'Parse Error';
    }

    return $price($value);
  })

  /**
   * Formats numbers with commas
   *
   * @param *number
   * @param int   Number of decimals to show
   *
   * @return string
   */
  ->registerHelper('number', function ($number, $options) {
    $decimals = 0;
    if (is_numeric($options)) {
      $decimals = $options;
    }

    return number_format((float) $number, $decimals);
  })

  /**
   * Formats the given number to it's short form
   *
   * Based of: https://gist.github.com/RadGH/84edff0cc81e6326029c
   *
   * @param *string number
   * @param *int precision
   */
  ->registerHelper('numshort', function ($number, $precision = 1) {
    if ($number < 900) {
      // 0 - 900
      $number_format = number_format($number, $precision);
      $suffix = '';
    } else if ($number < 900000) {
      // 0.9k-850k
      $number_format = number_format($number / 1000, $precision);
      $suffix = 'K';
    } else if ($number < 900000000) {
      // 0.9m-850m
      $number_format = number_format($number / 1000000, $precision);
      $suffix = 'M';
    } else if ($number < 900000000000) {
      // 0.9b-850b
      $number_format = number_format($number / 1000000000, $precision);
      $suffix = 'B';
    } else {
      // 0.9t+
      $number_format = number_format($number / 1000000000000, $precision);
      $suffix = 'T';
    }

    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
      $dotzero  = '.' . str_repeat('0', $precision);
      $number_format = str_replace($dotzero, '', $number_format);
    }

    return $number_format . $suffix;
  })

  /**
   * Formats numbers to price format
   *
   * @param *number
   *
   * @return string
   */
  ->registerHelper('price', function ($price) {
    return number_format((float) $price, 2);
  })

  /* i18n Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Uses the config/i18n folder to determine a translation
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('_', function ($key) {
    $args = func_get_args();
    $key = array_shift($args);
    $options = array_pop($args);

    $more = $options['fn']();
    if ($more) {
      $more = explode(' __ ', $more);
      foreach ($more as $arg) {
        $args[] = $arg;
      }
    }

    foreach ($args as $i => $arg) {
      if (is_null($arg)) {
        $args[$i] = '';
      }
    }

    return cradle('lang')->translate((string) $key, ...$args);
  })

  /**
   * Block for each timezone
   *
   * @return string
   */
  ->registerHelper('timezones', function ($options) {
    $abbreviations = DateTimeZone::listAbbreviations();

    //flatten multidimensional array
    $abbreviations = call_user_func_array('array_merge', $abbreviations);
    $buffer = [];
    foreach ($abbreviations as $abbreviation) {
      if (isset($buffer[$abbreviation['offset']])) {
        continue;
      }

      if (!trim($abbreviation['timezone_id'])) {
        continue;
      }

      $hours = $abbreviation['offset'] / 3600;
      $remainder = $abbreviation['offset'] % 3600;
      $sign = $hours > 0 ? '+' : '-';
      $hour = (int) abs($hours);
      $minutes = (int) abs($remainder / 60);

      if ($hour == 0 AND $minutes == 0) {
        $sign = ' ';
      }

      $buffer[$abbreviation['offset']] = $options['fn']([
        'name' => $abbreviation['timezone_id'],
        'offset' => $abbreviation['offset'],
        'gmt' => 'GMT' . $sign
          . str_pad($hour, 2, '0', STR_PAD_LEFT)
          . ':' . str_pad($minutes,2, '0'),
        'time_now' => gmdate('h:iA', time() + $abbreviation['offset'])
      ]);
    }

    ksort($buffer);
    return implode('', $buffer);
  })

  /* Date Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Formats the string to date format
   *
   * @param *string
   * @param string  Date format
   *
   * @return string
   */
  ->registerHelper('date', function ($time, $format, $options) {
    return date($format, strtotime($time));
  })

  /**
   * Formats the string to relative format
   *
   * @param *string
   * @param int   if 1, uses the mini relative format
   *
   * @return string
   */
  ->registerHelper('relative', function ($date, $options) {
    $timezone = cradle('tz');
    $offset = $timezone->getOffset();
    $relative = $timezone->toRelative(time() - $offset);

    if (!is_array($options) && $options) {
      $relative = strtolower($relative);

      $relative = str_replace(array('from now', 'ago'), '', $relative);
      $relative = str_replace(array('seconds', 'second'), 's', $relative);
      $relative = str_replace(array('minutes', 'minute'), 'm', $relative);
      $relative = str_replace(array('hours', 'hour'), 'h', $relative);
      $relative = str_replace(array('days', 'day'), 'd', $relative);
      $relative = str_replace(array('weeks', 'week'), 'w', $relative);
      $relative = str_replace(array('months', 'month'), 'm', $relative);
      $relative = str_replace(array('years', 'year'), 'y', $relative);
      $relative = str_replace(array('yesterday', 'tomorrow'), '1d', $relative);

      $relative = str_replace(' ', '', $relative);

      if (!preg_match('/^[0-9]+[a-z]+$/', $relative)) {
        return '';
      }
    }

    return $relative;
  })

  /* Array Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Joins an array together
   *
   * @param *array
   * @param string The connecting string
   *
   * @return string
   */
  ->registerHelper('join', function ($list, $separator, $options) {
    if(!is_array($list)) {
      return;
    }

    foreach ($list as $i => $variable) {
      if (is_array($variable)) {
        $list[$i] = implode(', ', $variable);
      }
    }

    return implode($separator, $list);
  })

  /**
   * Returns a JSON string
   *
   * @param *mixed
   * @param bool
   *
   * @return string
   */
  ->registerHelper('jsonify', function ($json, $pretty) {
    if ($pretty === 1) {
      return json_encode($json, JSON_PRETTY_PRINT);
    }

    return json_encode($json);
  })

  /**
   * Traverses into the specified array path
   *
   * @param *array       HTML string
   * @param string[..string] array path
   *
   * @return [BLOCK]
   */
  ->registerHelper('scope', function (...$args) {
    $options = array_pop($args);
    $array = array_shift($args);
    if (!is_array($array)) {
      return $options['inverse']();
    }

    $scope = Registry::i($array)->get(...$args);

    if (is_null($scope)) {
      $scope = Registry::i($array)->getDot(...$args);
    }

    if (is_null($scope)) {
      return $options['inverse']();
    }

    if (!is_array($scope)) {
      $scope = ['this' => $scope];
      $results = $options['fn']($scope);
      if (!$results) {
        return $scope['this'];
      }

      return $results;
    }

    $scope['this'] = $scope;
    return $options['fn']($scope);
  })

  /**
   * Splits a string into an array
   *
   * @param *string
   * @param string  The separating string
   *
   * @return [BLOCK]
   */
  ->registerHelper('split', function ($string, $separator, $options) {
    $list = explode($separator, $string);
    $list['this'] = $list;
    return $options['fn']($list);
  })

  /* URL Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Returns the hostbose
   *
   * ex. http://www.example.com/some/page.html (with no end slash)
   *
   * @return string
   */
  ->registerHelper('hostbase', function () {
    return cradle('host')->base();
  })

  /**
   * Returns the urldir
   *
   * ex. /some (with no end slash)
   *
   * @return string
   */
  ->registerHelper('hostdir', function () {
    return cradle('host')->dir();
  })

  /**
   * Returns the domain name
   *
   * ex. www.example.com
   *
   * @return string
   */
  ->registerHelper('hostdomain', function () {
    return cradle('host')->domain();
  })

  /**
   * Returns the hostname
   *
   * ex. http://www.example.com (with no end slash)
   *
   * @return string
   */
  ->registerHelper('hostname', function () {
    return cradle('host')->name();
  })

  /**
   * Returns the urlpath
   *
   * ex. /some/page.html (with no end slash)
   *
   * @return string
   */
  ->registerHelper('hostpath', function () {
    return cradle('host')->path();
  })

  /**
   * Returns the relative url
   *
   * ex. /some/page.html?foo=bar
   *
   * @return string
   */
  ->registerHelper('hostrelative', function () {
    return cradle('host')->relative();
  })

  /**
   * Returns the hosturl
   *
   * ex. http://www.example.com/some/page.html?foo=bar
   *
   * @return string
   */
  ->registerHelper('hosturl', function () {
    return cradle('host')->url();
  })

  /**
   * Uses a block to generate the pagination
   *
   * @param *int total
   * @param *int range
   *
   * @return [BLOCK]
   */
  ->registerHelper('pager', function ($total, $range, $options) {
    if ($range == 0) {
      return '';
    }

    $show = 10;
    $start = 0;

    if (isset($_GET['start']) && is_numeric($_GET['start'])) {
      $start = $_GET['start'];
    }

    $pages   = ceil($total / $range);
    $page   = floor($start / $range) + 1;

    $min   = $page - $show;
    $max   = $page + $show;

    if ($min < 1) {
      $min = 1;
    }

    if ($max > $pages) {
      $max = $pages;
    }

    //if no pages
    if ($pages <= 1) {
      return $options['inverse']();
    }

    $buffer = array();

    for ($i = $min; $i <= $max; $i++) {
      $_GET['start'] = ($i -1) * $range;

      $buffer[] = $options['fn'](array(
        'href'  => http_build_query($_GET),
        'active'  => $i == $page,
        'page'  => $i
      ));
    }

    return implode('', $buffer);
  })

  /**
   * Manipulates $_GET and returns the final query
   *
   * if 1 argument, will return the key value in $_GET (should be scalar)
   * if 2 or more arguments, will set the path and return the final query
   *
   * @param *string      key in $_GET
   * @param string[..string] path in $_GET to set
   * @param string       value to set
   *
   * @return string
   */
  ->registerHelper('query', function (...$names) {
    $options = array_pop($names);
    $registry = Registry::i($_GET);
    foreach ($names as $name) {
      $registry->removeDot($name);
    }

    return http_build_query($registry->get());
  })

  /**
   * Determines the caret to be used (needs fontawesome 5)
   *
   * @param *string[..string] array path to the sorting
   *
   * @return string
   */
  ->registerHelper('sortcaret', function (...$args) {
    $options = array_pop($args);
    $registry = Registry::i($_GET);

    $value = null;
    if ($registry->exists(...$args)) {
      $value = $registry->get(...$args);
    }

    $caret = null;
    if ($value === 'ASC') {
      $caret = '<i class="fas fa-caret-up"></i>';
    } else if ($value === 'DESC') {
      $caret = '<i class="fas fa-caret-down"></i>';
    }

    return $caret;
  })

  /**
   * Manipulates sort order and returns the final query
   *
   * @param *string[..string] array path to the sorting
   *
   * @return string
   */
  ->registerHelper('sorturl', function (...$args) {
    $options = array_pop($args);
    $registry = Registry::i($_GET);

    $value = null;
    if ($registry->exists(...$args)) {
      $value = $registry->get(...$args);
    }

    if (count($args) > 1) {
      $key = array_pop($args);
      $registry->remove(...$args);
      $args[] = $key;
    }

    if (is_null($value)) {
      $args[] = 'ASC';
      $registry->set(...$args);
    } else if ($value === 'ASC') {
      $args[] = 'DESC';
      $registry->set(...$args);
    }

    return http_build_query($registry->get());
  })

  /* Conditional Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Returns a default value2 if value1 is empty
   *
   * @param *scalar value
   * @param *scalar default
   *
   * @return *scalar
   */
  ->registerHelper('or', function ($value, $default) {
    // if value is not scalar, if empty or is null
    if (!is_scalar($value) || empty($value) || is_null($value)) {
      return $default;
    }

    return $value;
  })

  /**
   * A better if statement for handlebars
   *
   * @param *scalar first value
   * @param *string compare operator
   * @param *scalar second value
   *
   * @return [BLOCK]
   */
  ->registerHelper('when', function (...$args) {
    //$value1, $operator, $value2, $options
    $options = array_pop($args);
    $value2 = array_pop($args);
    $operator = array_pop($args);

    $value1 = array_shift($args);

    foreach ($args as $arg) {
      if (!isset($value1[$arg])) {
        $value1 = null;
        break;
      }

      $value1 = $value1[$arg];
    }

    $valid = false;

    switch (true) {
      case $operator == '=='   && $value1 == $value2:
      case $operator == '==='  && $value1 === $value2:
      case $operator == '!='   && $value1 != $value2:
      case $operator == '!=='  && $value1 !== $value2:
      case $operator == '<'  && $value1 < $value2:
      case $operator == '<='   && $value1 <= $value2:
      case $operator == '>'  && $value1 > $value2:
      case $operator == '>='   && $value1 >= $value2:
      case $operator == '&&'   && ($value1 && $value2):
      case $operator == '&&!'   && ($value1 && !$value2):
      case $operator == '!&&'   && (!$value1 && $value2):
      case $operator == '!&&!'   && (!$value1 && !$value2):
      case $operator == '||'   && ($value1 || $value2):
      case $operator == '||!'   && ($value1 || !$value2):
      case $operator == '!||'   && (!$value1 || $value2):
      case $operator == '!||!'   && (!$value1 || !$value2):
      case $operator == 'like'   && strpos($value1, $value2) !== false:
        $valid = true;
        break;
    }

    if ($valid) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * The opposite of the when helper above
   *
   * @param *scalar first value
   * @param *string compare operator
   * @param *scalar second value
   *
   * @return [BLOCK]
   */
  ->registerHelper('otherwise', function ($value1, $operator, $value2, $options) {
    $valid = false;

    switch (true) {
      case $operator == '=='   && $value1 == $value2:
      case $operator == '==='  && $value1 === $value2:
      case $operator == '!='   && $value1 != $value2:
      case $operator == '!=='  && $value1 !== $value2:
      case $operator == '<'  && $value1 < $value2:
      case $operator == '<='   && $value1 <= $value2:
      case $operator == '>'  && $value1 > $value2:
      case $operator == '>='   && $value1 >= $value2:
      case $operator == '&&'   && ($value1 && $value2):
      case $operator == '&&!'   && ($value1 && !$value2):
      case $operator == '!&&'   && (!$value1 && $value2):
      case $operator == '!&&!'   && (!$value1 && !$value2):
      case $operator == '||'   && ($value1 || $value2):
      case $operator == '||!'   && ($value1 || !$value2):
      case $operator == '!||'   && (!$value1 || $value2):
      case $operator == '!||!'   && (!$value1 || !$value2):
      case $operator == 'like'   && strpos($value1, $value2) !== false:
        $valid = true;
        break;
    }

    if ($valid) {
      return $options['inverse']();
    }

    return $options['fn']();
  })

  /**
   * Determines whether the array has a given key
   *
   * @param *array
   * @param mixed  value
   *
   * @return [BLOCK]
   */
  ->registerHelper('has', function ($array, $key, $options) {
    if (!is_array($array)) {
      return $options['inverse']();
    }

    if (isset($array[$key])) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * The opposite of the has helper above
   *
   * @param *array
   * @param mixed  value
   *
   * @return [BLOCK]
   */
  ->registerHelper('hasnt', function ($array, $key, $options) {
    if (!is_array($array)) {
      return $options['fn']();
    }

    if (isset($array[$key])) {
      return $options['inverse']();
    }

    return $options['fn']();
  })

  /**
   * Determines whether the array has a given value
   *
   * @param *array
   * @param mixed  value
   *
   * @return [BLOCK]
   */
  ->registerHelper('in', function (...$args) {
    $options = array_pop($args);
    $value = array_pop($args);

    $array = array_shift($args);

    if (is_string($array)) {
      $array = explode(',', $array);
    }

    foreach ($args as $arg) {
      if (!isset($array[$arg])) {
        $array = null;
        break;
      }

      $array = $array[$arg];
    }

    if (!is_array($array)) {
      return $options['inverse']();
    }

    if (in_array($value, $array)) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * The opposite of the in helper above
   *
   * @param *array
   * @param mixed  value
   *
   * @return [BLOCK]
   */
  ->registerHelper('notin', function ($array, $value, $options) {
    if (is_string($array)) {
      $array = explode(',', $array);
    }

    if (!is_array($array)) {
      return $options['fn']();
    }

    if (in_array($value, $array)) {
      return $options['inverse']();
    }

    return $options['fn']();
  })

  /**
   * Determines whether the param is an array
   *
   * @param mixed  param
   *
   * @return [BLOCK]
   */
  ->registerHelper('is_array', function ($array, $options) {
    if (is_array($array)) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * Determines whether the param is scalar
   *
   * @param mixed  param
   *
   * @return [BLOCK]
   */
  ->registerHelper('is_scalar', function ($scalar, $options) {
    if (is_scalar($scalar) || is_null($scalar)) {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * Determines whether the param is null
   *
   * @param mixed  param
   *
   * @return [BLOCK]
   */
  ->registerHelper('empty', function ($empty, $options) {
    if (is_null($empty) || $empty === '') {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /**
   * Determines whether the param is null
   *
   * @param mixed  param
   *
   * @return [BLOCK]
   */
  ->registerHelper('not_empty', function ($empty, $options) {
    if (!is_null($empty) && $empty !== '') {
      return $options['fn']();
    }

    return $options['inverse']();
  })

  /* Template Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Calls the compiler again to compile the given string (recursive)
   *
   * @param *string Handlebars template
   * @param *array  Variables to use
   *
   * @return string
   */
  ->registerHelper('compile', function ($template, $variables) {
    $template = cradle('handlebars')->compile($template);
    return $template($variables);
  })

  /**
   * Makes a default field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('detail_format', function ($schema, $name, $value) {
    if (isset($value['fn'])) {
      $value = null;
    }

    $template = '{{#format type schema item name}}{{{value}}}{{/format}}';
    $template = cradle('handlebars')->compile($template);

    return $template([
      'type' => 'detail',
      'schema' => $schema,
      'name' => $name,
      'item' => [$name => $value]
    ]);
  })

  /**
   * Makes a default field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('field_format', function ($schema, $name, $value) {
    if (isset($value['fn'])) {
      $value = null;
    }

    $template = '{{#format type schema item name}}{{{value}}}{{/format}}';
    $template = cradle('handlebars')->compile($template);

    return $template([
      'type' => 'field',
      'schema' => $schema,
      'name' => $name,
      'item' => [$name => $value]
    ]);
  })

  /**
   * Force outputs any handlebars variables
   *
   * @param *mixed[..mixed] Variables to output
   */
  ->registerHelper('inspect', function (...$args) {
    $options = array_pop($args);

    $template = '<h6>Argument %s:</h6><pre class="inspector">%s</pre>';

    $inspectors = [];
    foreach ($args as $i => $arg) {
      $inspectors[] = sprintf($template, $i, var_export($arg, true));
    }

    return sprintf(
      '<h5>Handlebars Inspector<h5>%s',
      implode('', $inspectors)
    );
  })

  /**
   * Makes a default field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('list_format', function ($schema, $name, $value) {
    if (isset($value['fn'])) {
      $value = null;
    }

    $template = '{{#format type schema item name}}{{{value}}}{{/format}}';
    $template = cradle('handlebars')->compile($template);

    return $template([
      'type' => 'detail',
      'schema' => $schema,
      'name' => $name,
      'item' => [$name => $value]
    ]);
  })

  /**
   * Calls the compiler again to compile the given partial (recursive)
   *
   * @param *string Name of partial
   * @param *array  Variables to use
   *
   * @return string
   */
  ->registerHelper('partial', function ($name, $variables) {
    $handlebars = cradle('handlebars');
    $partial = $handlebars->getPartial($name);
    $template = $handlebars->compile($partial);

    return $template($variables);
  })

  /* Framework Helpers
  ------------------------------------------------------------------------------*/

  /**
   * Gives access to the current request object
   *
   * @param *string[..string] request path
   *
   * @return mixed|[BLOCK]
   */
  ->registerHelper('config', function (...$args) {
    $options = array_pop($args);

    $results = cradle('config')->get(...$args);

    if (!$results) {
      return $options['inverse']();
    }

    if (is_object($results) || is_array($results)) {
      return $options['fn']((array) $results);
    }

    return $results;
  })

  /**
   * Gives access to the current request object
   *
   * @param *string[..string] request path
   *
   * @return mixed|[BLOCK]
   */
  ->registerHelper('request', function (...$args) {
    $options = array_pop($args);

    $results = cradle()->getRequest()->get(...$args);

    if (!$results) {
      return $options['inverse']();
    }

    if (is_object($results) || is_array($results)) {
      return $options['fn']((array) $results);
    }

    return $results;
  })

  /**
   * Gives access to the current response object
   *
   * @param *string[..string] request path
   *
   * @return mixed|[BLOCK]
   */
  ->registerHelper('response', function (...$args) {
    $options = array_pop($args);

    $results = cradle()->getResponse()->get(...$args);

    if (!$results) {
      return $options['inverse']();
    }

    if (is_object($results) || is_array($results)) {
      return $options['fn']((array) $results);
    }

    return $results;
  })

;
