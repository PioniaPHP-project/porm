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


//use Porm\database\aggregation\Agg;
//use Porm\database\builders\Where;
//use Porm\Porm;

require __DIR__ . '/../vendor/autoload.php';


//$var = Porm::from('user')
//    ->filter(['role_id' => 1])
//    ->first();
//
//var_dump($var);

//$inserted = $database::from("qa_criteria")->save([
//    'name' => 'Test 22',
//    'description' => 'Test Description 2 ',
//    'best_of_total' => 20
//]);
//
//var_dump($inserted);

//$uodate = Porm::from("qa_criteria")
//    ->update(Agg::plus("best_of_total", 3), 25);
//
//var_dump($uodate->rowCount());


//$var = Porm::from("qa_criteria")
//    ->delete(Where::builder()->or(Agg::builder()
//        ->like("name", '28')
//        ->like("description", '28')
//        ->build())->build());
//
//var_dump($var->rowCount());


// select * from qa_criteria
//$select = Porm::from('qa_criteria')
//    ->all();
//
//var_dump($select);

// get the first user with last_name Ezra
//$first = Porm::from('user')
//    ->filter(['last_name' => 'Ezra'])
//    ->first();
//
//var_dump($first);

// insert into qa_criteria
//$inserted = Porm::from("qa_criteria")->save([
//    'name' => 'Test 28',
//    'description' => 'Test Description 28 ',
//    'best_of_total' => 20
//]);
//// inserted is the object we just inserted
//var_dump($inserted);

// update qa_criteria set best_of_total = best_of_total + 3 where id = 25
//$updated = Porm::from("qa_criteria")
//    ->update(Agg::builder()->plus('best_of_total', 3)->build(), Where::builder()->where(['id' => 30])->build());
//
//// updated will consist of the number of rows updated
//var_dump($updated->rowCount());


// delete from qa_criteria where id = 24
//$deleted = Porm::from("qa_criteria")
//    ->deleteById(26);
//
//var_dump($deleted->rowCount());

// select with offset and limits in mind
//$select = Porm::from('dev_job_category')
//    ->filter() // must call filter first
//    ->limit(10)
//    ->startAt(2)
//    ->all(); // must call all or first or get last to actually execute the query
//
//// $select is an array of 10 objects starting from the 3rd object
//var_dump($select);

// with joins in mind
//$select = Porm::from('user')
//    ->addAggregateWhere(\Porm\database\aggregation\Agg::like('first_name', 'ezra'))
//    ->random();
//
//var_dump($select);

//$var = Porm::from("user")->filter(Where::builder()
//    ->and(Where::builder()
//        ->or(
//            Agg::builder()
//                ->like("first_name", "jet")
//                ->lt("password", 10)
//                ->build()
//        )
//        ->or(
//            ["last_name" => 10, 'first_name' => 'nothing']
//        )->build())
//    ->build()
//)->all();

//var_dump($var);

// order by
//$var = Porm::from("qa_criteria")
//    ->filter()
//    ->orderBy(["best_of_total" => 'DESC'])
//    ->all();
//
//var_dump($var);

