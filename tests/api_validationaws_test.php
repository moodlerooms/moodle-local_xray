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

defined('MOODLE_INTERNAL') || die();


require_once(__DIR__.'/base.php');

/**
 * Class local_xray_login_test
 *
 * All tests in this class will fail in case there is no appropriate fixture to be loaded.
 *
 * @group local_xray
 */
class local_xray_api_validationaws_testcase extends local_xray_base_testcase {

    const PLUGIN = 'local_xray';

    /**
     * @return void
     */
    public function setUp(): void {
        $this->reset_ws();
    }

    /**
     * @return void
     */
    public function test_check_ws_connect_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();
        // Tell the cache to load specific fixture for login url.
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/accesstoken', 'user-accesstoken-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com', 'user-accountcheck-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo', 'domain-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo/course', 'courses-final.json');
        $this->assertTrue( \local_xray\local\api\validationaws::check_ws_connect()->is_successful() );
    }

    /**
     * @return void
     */
    public function test_check_ws_connect_login_fail() {
        $this->resetAfterTest(true);
        $this->config_set_ok();
        // Tell the cache to load specific fixture for login url.
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-fail-final.json');
        $result = \local_xray\local\api\validationaws::check_ws_connect()->get_result();
        $this->assertRegexp( '/'.get_string('error_wsapi_reason_login', self::PLUGIN).'/', $result[0]);
    }

}
