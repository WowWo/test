<?php

use Illuminate\Database\Seeder;

class EmployeeTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $file = base_path('employee_table.csv');
        $file = DIRECTORY_SEPARATOR === '/' ? $file : str_replace('\\', '/', $file);
        $query = "LOAD DATA INFILE '" . $file . "'
    INTO TABLE employee
    FIELDS TERMINATED BY ';'
    LINES TERMINATED BY '\r\n'
    IGNORE 1 LINES;";
        DB::connection()->getpdo()->exec($query);
    }

}
