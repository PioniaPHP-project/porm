<?php

/**
 * PORM - Database querying tool for pionia framework.
 *
 * This package can be used as is or with the Pionia Framework. Anyone can reproduce and update this as they see fit.
 *
 * @copyright 2024,  Pionia Project - Jet Ezra
 *
 * @author Jet Ezra
 * @version 1.0.0
 * @link https://pionia.netlify.app/
 * @license https://opensource.org/licenses/MIT
 *
 **/


use Porm\Porm;
use Porm\queryBuilder\JoinTypes;

require __DIR__ . '/../vendor/autoload.php';

$database = new Porm('db');

//$var = Porm::from('user')
//    ->join(JoinTypes::LEFT, 'profile', 'role_id')
//    ->filter(['role_id' => 1])
//    ->first();

//$inserted = $database::from("qa_criteria")->save([
//    'name' => 'Test 22',
//    'description' => 'Test Description 2 ',
//    'best_of_total' => 20
//]);
//
//var_dump($inserted);

$uodate = $database::from("qa_criteria")
    ->update(\Porm\queryBuilder\Parg::plus("best_of_total", 3), 25);

//$var = $database::from("qa_criteria")
//    ->delete(24);
//
//var_dump($var->rowCount());
