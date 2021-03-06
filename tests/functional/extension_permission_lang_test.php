<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_extension_permission_lang_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static protected $fixtures = array(
		'foo/bar/language/en/',
	);

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp()
	{
		parent::setUp();
		
		$this->get_db();
		
		$acl_ary = array(
			'auth_option'	=> 'u_foo',
			'is_global'		=> 1,
		);

		$sql = 'INSERT INTO phpbb_acl_options ' . $this->db->sql_build_array('INSERT', $acl_ary);
		$this->db->sql_query($sql);

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
	}

	public function test_auto_include_permission_lang_from_extensions()
	{
		$this->phpbb_extension_manager->enable('foo/bar');

		// User permissions
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = self::submit($form);

		// language from language/en/acp/permissions_phpbb.php
		$this->assertContains('Can attach files', $crawler->filter('body')->text());

		// language from ext/foo/bar/language/en/permissions_foo.php
		$this->assertContains('Can view foo', $crawler->filter('body')->text());
	}
}
