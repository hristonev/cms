<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 04.09.17
 * Time: 13:47
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ParkingController extends Controller
{
    /**
     * @Route("parking")
     */
    public function indexAction()
    {
        return $this->render("parking.html.twig", [
            'name' => 'Камиони',
            'rows' => 2,
            'position' => [
                'X9950BM' => ['pos' => 1, 'contract' => false],
                'X2073EE' => ['pos' => 2, 'contract' => false],
                'X9484EE' => ['pos' => 3, 'contract' => true],
                'PB7000CH' => ['pos' => 4, 'contract' => false],
                'X6551BC' => ['pos' => 5, 'contract' => false],
                'X8232KA' => ['pos' => 6, 'contract' => false],
                'X7584BC' => ['pos' => 7, 'contract' => false],
                'X9726EE' => ['pos' => 8, 'contract' => true],
                'X5561BC' => ['pos' => 9, 'contract' => false],
                'X9652EE' => ['pos' => 10, 'contract' => false],
                'X4998BB' => ['pos' => 11, 'contract' => false],
                'X0419EM' => ['pos' => 12, 'contract' => false],
                'X5155BB' => ['pos' => 13, 'contract' => false],
                'X7177EE' => ['pos' => 14, 'contract' => false],
                'X0872BC' => ['pos' => 15, 'contract' => false],
                'PB5205AT' => ['pos' => 16, 'contract' => false],
                'X5308BC' => ['pos' => 17, 'contract' => false],
                'X6143BC' => ['pos' => 18, 'contract' => false],
                'X7716BP' => ['pos' => 19, 'contract' => true],
                'X1117AP' => ['pos' => 20, 'contract' => true],
                'CA1643MB' => ['pos' => 21, 'contract' => true],
                'C3549EP' => ['pos' => 22, 'contract' => true],
                'X3767BA' => ['pos' => 23, 'contract' => false],
                'X9688BA' => ['pos' => 24, 'contract' => true],
                'X2292EE' => ['pos' => 25, 'contract' => false],
                'X9411EE' => ['pos' => 26, 'contract' => true],
                'X7997BP' => ['pos' => 27, 'contract' => true],
                'X7836BC' => ['pos' => 28, 'contract' => true],
                'X9736EE' => ['pos' => 29, 'contract' => true],
                'X1877AP' => ['pos' => 30, 'contract' => true],
                'X4052AH' => ['pos' => 31, 'contract' => false],
                'X8333BC' => ['pos' => 32, 'contract' => false],
                'X8222BC' => ['pos' => 33, 'contract' => false],
                'X2084EE' => ['pos' => 34, 'contract' => false],
                'X7232BC' => ['pos' => 35, 'contract' => false],
                'X9751EE' => ['pos' => 36, 'contract' => false],
                'X4928BT' => ['pos' => 37, 'contract' => false],
                'X0401BH' => ['pos' => 38, 'contract' => false],
                'X7834BC' => ['pos' => 39, 'contract' => false],
                'X9738EE' => ['pos' => 40, 'contract' => false],
                'X9444BC' => ['pos' => 41, 'contract' => false],
                'X1234AB' => ['pos' => 42, 'contract' => false],
            ]
        ]);
    }
}