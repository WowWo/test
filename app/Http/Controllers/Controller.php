<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use App\Department;
use App\Employee;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
  public function one()
  {
      
    //------------------------------------------------------------------------------------------------------------------------------
    //2
    $q2 = Department::leftJoin('employee', 'employee.dep_id', '=', 'department.id')
            ->select('department.department', DB::raw('max(employee.salary) max_salary'), DB::raw('sum(employee.salary) sum_salary'))
            ->groupBy('department.id') 
            ->having(DB::raw('min(employee.salary)'), '>', 1000)
            ->get();
    //2*
    $q2n = Department::leftJoin('employee', 'employee.dep_id', '=', 'department.id')
            ->select('department.department', DB::raw('max(employee.salary) max_salary'), DB::raw('sum(employee.salary) sum_salary'),
            DB::raw('(select e2.full_name 
                from employee e2 where e2.salary = (
    select max(e3.salary)
    from employee e3  where department.id = e3.dep_id 
)
 limit 1) salary_max_name'))
            ->groupBy('department.id') 
            ->having(DB::raw('min(employee.salary)'), '>', 1000)
            ->get();
    //3
    $department ='department.id';
    $q3_sub = Employee::select('salary',DB::raw("count(1) over (partition by 'a') total_rows"),DB::raw("row_number() over (order by salary asc) salary_order"))->toSql();
    $q3 =  DB::table(DB::raw("($q3_sub) e"))->select('e.salary')->where('e.salary_order', '=', DB::raw('round(e.total_rows / 2.0, 0)'))->get();
    //3*
    $q3n_sub1_1 = Employee::select('salary',DB::raw("count(1) over (partition by 'a') total_rows"),DB::raw("row_number() over (order by salary asc) salary_order"))
            ->where('dep_id','=', DB::raw($department))
            ->toSql();
    $q3n_sub1 = DB::table(DB::raw("($q3n_sub1_1) e"))->select('e.salary')->where('e.salary_order', '=', DB::raw('round(e.total_rows / 2.0, 0)'))->toSql();
    $q3n_sub2 = Employee::select(DB::raw('sum(salary)'))->where('dep_id','=', DB::raw($department))->toSql();
    $q3n_sub3 = Employee::select(DB::raw('round(avg(salary))'))->where('dep_id', DB::raw($department))->toSql();
    $q3n = Department::select('department',DB::raw("($q3n_sub1) median"), DB::raw("($q3n_sub2) sum"), DB::raw("($q3n_sub3) avg"))->get();
    $laravel_query = '2: '.$q2 . '</br>' .'2*: '.$q2n . '</br>' .'3: '.$q3 . '</br>' .'3*: '.$q3n;
    //----------------------------------------------------------------------------------------------------------------------------
    $q1 = "
DROP TABLE IF EXISTS employee;
DROP TABLE IF EXISTS department;
CREATE TABLE department (
  id int(11) NOT NULL AUTO_INCREMENT,
  department varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE employee (
  id int(11) NOT NULL AUTO_INCREMENT,
  dep_id int(11) NOT NULL,
  full_name varchar(255) NOT NULL,
  salary int(11) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (dep_id)  REFERENCES department (id)
);
LOAD DATA INFILE 'D:\department_table.csv'
INTO TABLE department
FIELDS TERMINATED BY ';'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES;

LOAD DATA INFILE 'D:\employee_table.csv'
INTO TABLE employee
FIELDS TERMINATED BY ';'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES;";
        //2
        $q2 = "SELECT d.department,
       max(e.salary),
       sum(e.salary)
FROM employee e
LEFT JOIN department d ON d.id = e.dep_id
GROUP BY e.dep_id
HAVING min(e.salary)>1000";
        //2*
        $q2n = "SELECT d.department,
       max(e.salary),
       sum(e.salary),
  (SELECT full_name
   FROM employee e2
   WHERE e2.salary =
       (SELECT max(e3.salary)
        FROM employee e3
        WHERE d.id = e3.dep_id )
   LIMIT 1) name
FROM employee e
LEFT JOIN department d ON d.id = e.dep_id
GROUP BY e.dep_id
HAVING min(e.salary)>1000";
    //3
    $q3 = "SELECT e.salary
FROM
  (SELECT salary,
          Count(1) OVER (PARTITION BY 'A') total_rows,
                        Row_number() OVER (
                                           ORDER BY salary ASC) salary_order
   FROM employee) e
WHERE e.salary_order = Round(e.total_rows / 2.0, 0)";
    //3*
    $q3n = "SELECT department,

  (SELECT e.salary
   FROM
     (SELECT salary,
             dep_id,
             count(1) OVER (PARTITION BY 'a') total_rows,
                           row_number() OVER (
                                              ORDER BY salary ASC) salary_order
      FROM employee
      WHERE dep_id=d.id) e
   WHERE e.salary_order = round(e.total_rows / 2.0, 0)) median,
  (SELECT sum(salary)
   FROM employee
   WHERE dep_id=d.id) SUM,
  (SELECT round(avg(salary))
   FROM employee
   WHERE dep_id=d.id) AVG
FROM department d";
    $sql_query = '1: '.$q1 . '</br>' . '2: '.$q2 . '</br>' .'2*: '.$q2n . '</br>' .'3: '.$q3 . '</br>' .'3*: '.$q3n;
    return $laravel_query . '<br/><br/><br/>'. $sql_query;
  }
}
