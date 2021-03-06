<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */


// Call ProjectTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "ProjectTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once "testConf.php";

$_SESSION['WPATH'] = WPATH;

require_once ROOT_PATH."/lib/confs/Conf.php";
require_once ROOT_PATH . '/lib/common/UniqueIDGenerator.php';
require_once 'Projects.php';

/**
 * Test class for Project.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-22 at 15:43:08.
 */
class ProjectTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */

    public $classProject = null;
    public $connection = null;

    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("ProjectTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {

    	$this->classProject = new Projects();

    	$conf = new Conf();
    	$this->connection = mysql_connect($conf->dbhost.":".$conf->dbport, $conf->dbuser, $conf->dbpass);
        mysql_select_db($conf->dbname);

        mysql_query("TRUNCATE TABLE `ohrm_project`", $this->connection);

        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1001','zanfer1','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1002','zanfer2','forrw',0 )");
        mysql_query("INSERT INTO `ohrm_customer` VALUES ('1003','zanfer3','forrw',0 )");

        mysql_query("INSERT INTO `ohrm_project` VALUES ('1001','1001','p1','w',0 )");
        mysql_query("INSERT INTO `ohrm_project` VALUES ('1002','1002','p2','w',0 )");
        mysql_query("INSERT INTO `ohrm_project` VALUES ('1003','1003','p3','w',0 )");
		UniqueIDGenerator::getInstance()->initTable();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
	protected function tearDown() {

	    mysql_query("TRUNCATE TABLE `ohrm_project`", $this->connection);

		mysql_query("DELETE FROM `ohrm_customer` WHERE `customer_id` IN (1001, 1002, 1003);", $this->connection);
		UniqueIDGenerator::getInstance()->initTable();
    }

    public function testFetchProject() {

    	$res  = $this->classProject->fetchProject("1001");

    	$this->assertNotNull($res, "No record found");

    	$this->assertEquals($res->getProjectId(),'1001','Invalid project id');
	   	$this->assertEquals($res->getCustomerId(),'1001','Invalid customer id');
	   	$this->assertEquals($res->getProjectName(),'p1','Invalid description');
	   	$this->assertEquals($res->getProjectDescription(),'w','Invalid description');
	   	$this->assertEquals($res->getDeleted(), Projects::PROJECT_NOT_DELETED,'Invalid description');
    }

    public function testAddProject() {

    	$this->classProject->setCustomerId("1003");
    	$this->classProject->setProjectName("Dodle");
    	$this->classProject->setProjectDescription("jhgjhg");

    	$res  = $this->classProject->addProject();
    	$id = $this->classProject->getProjectId();
    	$this->assertTrue($res, "Adding failed");

    	$res  = $this->classProject->fetchProject($id);
	    $this->assertNotNull($res, "No record found");

	   	$this->assertEquals($res->getProjectId(), $id,'Invalid project id');
	   	$this->assertEquals($res->getCustomerId(),'1003','Invalid customer id');
	   	$this->assertEquals($res->getProjectName(),'Dodle','Invalid description');
	   	$this->assertEquals($res->getProjectDescription(),'jhgjhg','Invalid description');
	   	$this->assertEquals($res->getDeleted(), Projects::PROJECT_NOT_DELETED,'Invalid description');
    }

	public function testFetchProjects() {

      	$res = $this->classProject->fetchProjects();
      	$this->assertNotNull($res, "record Not found");

      	$this->assertEquals(count($res), 3,'count incorrect');

      	$expected[0] = array('1001', '1001', 'p1', 'w', Projects::PROJECT_NOT_DELETED);
      	$expected[1] = array('1002', '1002', 'p2', 'w', Projects::PROJECT_NOT_DELETED);
      	$expected[2] = array('1003', '1003', 'p3', 'w', Projects::PROJECT_NOT_DELETED);

      	$i= 0;

		for ($i=0; $i<count($res); $i++) {

			$this->assertEquals($expected[$i][0], $res[$i]->getProjectId(), 'Wrong Project Request Id');
			$this->assertEquals($expected[$i][1], $res[$i]->getCustomerId(), 'Wrong Cus Id ');
			$this->assertEquals($expected[$i][2], $res[$i]->getProjectName(), 'Wrong Project Name ');
			$this->assertEquals($expected[$i][3], $res[$i]->getProjectDescription(),'Wrong Project Description ');
			$this->assertEquals($expected[$i][4], $res[$i]->getDeleted(),'Invalid description');
      	}

      	// Delete one project
      	mysql_query("UPDATE `ohrm_project` SET is_deleted = 1 WHERE project_id = 1001");

      	// By default, all projects are returned
      	$res = $this->classProject->fetchProjects();
      	$this->assertNotNull($res, "record Not found");
      	$this->assertEquals(count($res), 3,'count incorrect');

      	// Fetch only NOT DELETED projects
      	$res = $this->classProject->setDeleted(Projects::PROJECT_NOT_DELETED);
      	$res = $this->classProject->fetchProjects();
      	$this->assertNotNull($res, "record Not found");
      	$this->assertEquals(count($res), 2,'count incorrect');

	}

	public function testGetListOfProjects() {

      	$res = $this->classProject->fetchProjects();
      	$this->assertNotNull($res, "record Not found");

      	$this->assertEquals(count($res), 3,'count incorrect');

      	$expected[0] = array('1001', '1001', 'p1', 'w', Projects::PROJECT_NOT_DELETED);
      	$expected[1] = array('1002', '1002', 'p2', 'w', Projects::PROJECT_NOT_DELETED);
      	$expected[2] = array('1003', '1003', 'p3', 'w', Projects::PROJECT_NOT_DELETED);

      	$i= 0;

		for ($i=0; $i<count($res); $i++) {

			$this->assertEquals($expected[$i][0], $res[$i]->getProjectId(), 'Wrong Project Request Id');
			$this->assertEquals($expected[$i][1], $res[$i]->getCustomerId(), 'Wrong Cus Id ');
			$this->assertEquals($expected[$i][2], $res[$i]->getProjectName(), 'Wrong Project Name ');
			$this->assertEquals($expected[$i][3], $res[$i]->getProjectDescription(),'Wrong Project Description ');
			$this->assertEquals($expected[$i][4], $res[$i]->getDeleted(),'Invalid description');
      	}
	}

	public function testUpdateProject() {
    	$res = $this->classProject->fetchProject("1001");

    	$res->setCustomerId('1002');

    	$res = $res->updateProject();

    	$this->assertTrue($res, "Adding failed");

    	$res = $this->classProject->fetchProject("1001");
    	$this->assertNotNull($res, "No record found");

    	$this->assertEquals($res->getProjectId(),'1001','Invalid project id');
	   	$this->assertEquals($res->getCustomerId(),'1002','Invalid customer id');
	   	$this->assertEquals($res->getProjectName(),'p1','Invalid description');
	   	$this->assertEquals($res->getProjectDescription(),'w','Invalid description');
	   	$this->assertEquals($res->getDeleted(), Projects::PROJECT_NOT_DELETED,'Invalid description');
	}

	public function testUpdateProject2() {
    	$res = $this->classProject->fetchProject("1001");

		$res->setCustomerId('1002');
    	$res->setProjectName('px');
    	$res->setProjectDescription('ogg');

    	$res = $res->updateProject();

    	$this->assertTrue($res, "Adding failed");

    	$res = $this->classProject->fetchProject("1001");
    	$this->assertNotNull($res, "No record found");

    	$this->assertEquals($res->getProjectId(),'1001','Invalid project id');
	   	$this->assertEquals($res->getCustomerId(),'1002','Invalid customer id');
	   	$this->assertEquals($res->getProjectName(),'px','Invalid description');
	   	$this->assertEquals($res->getProjectDescription(),'ogg','Invalid description');
	   	$this->assertEquals($res->getDeleted(), Projects::PROJECT_NOT_DELETED,'Invalid description');
	}

	public function testDeleteProject() {
		$this->classProject->setProjectId("1001");

		$res = $this->classProject->deleteProject();

		$this->assertTrue($res, "Adding failed");

    	$res = $this->classProject->fetchProject("1001");
    	$this->assertNotNull($res, "No record found");

    	$this->assertEquals($res->getProjectId(),'1001','Invalid project id');
	   	$this->assertEquals($res->getCustomerId(),'1001','Invalid customer id');
	   	$this->assertEquals($res->getProjectName(),'p1','Invalid description');
	   	$this->assertEquals($res->getProjectDescription(),'w','Invalid description');
	   	$this->assertEquals($res->getDeleted(), Projects::PROJECT_DELETED,'Invalid description');
	}
	
	public function testRetrieveProjectName() {
		
		$actual = $this->classProject->retrieveProjectName(1001);
		$this->assertEquals('p1', $actual);
		
		$actual = $this->classProject->retrieveProjectName(1009);
		$this->assertEquals('', $actual);
				
	}
	
	public function testRetrieveCustomerName() {
		
		$actual = $this->classProject->retrieveCustomerName(1001);
		$this->assertEquals('zanfer1', $actual);
		
		$actual = $this->classProject->retrieveCustomerName(1009);
		$this->assertEquals('', $actual);

	}

}

// Call ProjectTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "ProjectTest::main") {
    ProjectTest::main();
}

?>
