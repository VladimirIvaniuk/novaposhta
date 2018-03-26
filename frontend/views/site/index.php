<?php

/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use LisDev\Delivery\NovaPoshtaApi2;
$this->title = 'Расчет стоимости доставки';
?>
<?php $form=ActiveForm::begin()?>
<div class="site-index">
    Отпровитель
    <br>
        <select name="sender_cities" size="1" class="sender_cities">
            <option value="0">--Выбрать город--</option>
            <?php foreach ($cities['data'] as $city) :?>
                <option value="<?=$city['Ref']?>"><?=$city['DescriptionRu']?></option>
            <?php endforeach;?>
        </select>
        <select name="warehouses" size="1" class="warehouses">
            <option value="0">--Выбрать отделение--</option>
        </select>
        <br>
        <br>
    Получатель
        <br>
            <select name="recipient_city" size="1" class="recipient_city">
                <option value="0">--Выбрать город--</option>
                <?php foreach ($cities['data'] as $city) :?>
                    <option value="<?=$city['Ref']?>"><?=$city['DescriptionRu']?></option>
                <?php endforeach;?>
            </select>
            <select name="warehouses2" size="1" class="warehouses2">
                <option value="0">--Выбрать отделение--</option>
            </select>
        <br>
    </div>
    <br>
<div>
    <label class="result"></label>
</div>

<?php $url=Url::to(['site/test'])?>
<!--Получаем выбранный город отпровителя-->
<!--И передаем данные в отделения отпровителя-->
<?php $script = <<< JS
   $(document).ready(function () {
    $('.sender_cities').change(function () {
        var wh = $(this).val();
         $.ajax({
            url : '{$url}',
            type : 'POST',
            data : {
                'sender_cities' : wh,
            },
            success : function(data) {
                $('.warehouses').html(data);
            }
            // error : function(request,error)
            // {
            //     $('#warehouses').html('<option>-</option>');
            // }
        });
    });
})
JS;
$this->registerJs($script);
//Выбераем отделение отпровителя и передаем на action Тест
$script2 = <<< JS
   $(document).ready(function () {
    $('.warehouses').change(function () {
        var wh = $(this).val();
         $.ajax({
            url : '{$url}',
            type : 'POST',
            data : {
                'sender_city_ref' : wh,
            },
        });
    });
})
JS;
$this->registerJs($script2);
//Получаем выбранный город получателя
//И передаем данные в отделения получателя
$script3 = <<< JS
   $(document).ready(function () {
    $('.recipient_city').change(function () {
        var wh = $(this).val();
         $.ajax({
            url : '{$url}',
            type : 'POST',
            data : {
                'recipient_city' : wh,
            },
            success : function(data) {
                $('.warehouses2').html(data);
            }
        });
    });
})
JS;
$this->registerJs($script3);
//Выбераем отделение получателя и передаем на action Тест
$script4 = <<< JS
   $(document).ready(function () {
    $('.warehouses2').click(function () {
        var wh = $(this).val();
         $.ajax({
            url : '{$url}',
            type : 'POST',
            data : {
                'recipient_city_ref' : wh,
            },
            success : function(data) {
                $('.result').html(data);
            }
        });
    });
})
JS;
$this->registerJs($script4);


//$script5 = <<< JS
//$(document).ready(function () {
//    $('#my_btn').click(function () {
//        var wh = 33;
//        alert('hi');
////        $.ajax({
////            url : '{$url}',
////            type : 'POST',
////            data : {
////            'recipient_city_ref' : wh,
////            },
////            success : function(data) {
////            $('.result').html(data);
////        }
//       });
//    });
//JS;
//$this->registerJs($script5);
?>