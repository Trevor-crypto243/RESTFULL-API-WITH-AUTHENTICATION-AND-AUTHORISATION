<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeIncome extends Model
{
    protected $fillable = ['employer_id','payroll_no','id_no','gross_salary','basic_salary','net_salary','employment_date'];
}
