<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MUC support.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/* @noinspection PhpIncludeInspection */
require_once($CFG->libdir.'/filelib.php');

/**
 * Class cache
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cache {
    /**
     * @var \cache_application|null
     *
     */
    protected $muc = null;

    public function __construct() {
        if (PHPUNIT_TEST) {
            return;
        }
        $this->muc = \cache::make('local_xray', 'request');
    }

    /**
     * @return int
     */
    public static function cache_timeout() {
        static $result = null;
        if ($result === null) {
            $result = (int)get_config('local_xray', 'curlcache') * HOURSECS;
            $result += (int)get_config('local_xray', 'curlcache_minutes') * MINSECS;
        }
        return $result;
    }

    /**
     * @return int
     */
    public static function cache_timeout_hours() {
        $result = (int)round(self::cache_timeout() / HOURSECS);
        return $result;
    }

    /**
     * @param  mixed  $seed
     * @return string
     */
    protected function getkeyname($seed) {
        $result = md5($seed);
        return $result;
    }

    /**
     * @param mixed $seed
     * @return array
     */
    protected function getkeys($seed) {
        $basekeyname = $this->getkeyname($seed);
        $result = [$basekeyname, $basekeyname.'_created'];
        return $result;
    }

    /**
     * @param mixed $param
     * @return bool|string
     */
    public function get($param) {
        if (PHPUNIT_TEST) {
            $result = testhelper::get_fixture_data($param);
            if ($result) {
                xrayws::instance()->setcookie('connect.sid=somecookievalue');
            }
            return $result;
        }
        $result = false;
        if (self::cache_timeout() == 0) {
            return $result;
        }
        $keyarray = $this->getkeys($param);
        list($key, $created) = $keyarray;
        $items = $this->muc->get_many($keyarray);
        if (!empty($items[$key])) {
            if ((time() - $items[$created]) > self::cache_timeout()) {
                $this->muc->delete_many($keyarray);
                return $result;
            }
        }
        if (!empty($items[$key])) {
            $result = unserialize($items[$key]);
        }
        return $result;
    }

    /**
     * @param mixed $param
     * @param mixed $val
     * @return void
     */
    public function set($param, $val) {
        if (PHPUNIT_TEST) {
            return;
        }
        if (self::cache_timeout() > 0) {
            list($key, $created) = $this->getkeys($param);
            $this->muc->set_many([$key => serialize($val), $created => time()]);
        }
    }

    /**
     * @param mixed $param
     * @return int
     */
    public function delete($param) {
        if (PHPUNIT_TEST) {
            return 0;
        }
        return $this->muc->delete_many($this->getkeys($param));
    }

    /**
     * @return bool
     */
    public function refresh() {
        if (PHPUNIT_TEST) {
            return false;
        }
        return $this->muc->purge();
    }
}
