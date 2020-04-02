<?php
/**
 * BoardController.php
 *
 * Board Controller
 *
 * @category   Test
 * @package    Api
 * @author     Christian Acevedo
 */

namespace Tests;

use App\Controller\BoardController;
use PHPUnit\Framework\TestCase;

/**
 * Class BoardController
 *
 */
class BoardControllerTest extends TestCase
{

    //Launching local test:  php ./vendor/bin/phpunit tests/BoardControllerTest.php
    public function testReturnArray()
    {
        $calculator = new BoardController();
        $result = $calculator->returnArray();

        // assert that your calculator added the numbers correctly!
        $this->assertEquals([], $result);
    }


}