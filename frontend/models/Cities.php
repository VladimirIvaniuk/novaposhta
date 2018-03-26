<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 19.03.2018
 * Time: 15:16
 */

namespace frontend\models;


use yii\db\ActiveRecord;

class Cities extends ActiveRecord
{
     public static function tableName()
     {
         return '{{Cities}}';
     }

//     public function rules()
//     {
//         return[
//             [['city_name', 'raf'], 'required'],
//         ];
//     }

}