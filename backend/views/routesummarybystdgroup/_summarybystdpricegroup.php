<?php

use kartik\daterange\DateRangePicker;

$product_header = [];
$route_name = '';

$find_date = date('Y-m-d');
if ($route_id != null) {
    $route_name = \backend\models\Deliveryroute::findName($route_id);
}
if($search_date !=null){
    $find_date = date('Y-m-d',strtotime($search_date));
}

//
//    $modelx = \common\models\QuerySaleByDistributor::find()->select(['product_id'])->join('inner join', 'product', 'query_sale_by_distributor.product_id=product.id')->where(['BETWEEN', 'date(order_date)', date('Y-m-d', strtotime($from_date)), date('Y-m-d', strtotime($to_date))])
//        ->andFilterWhere(['product.company_id' => $company_id, 'product.branch_id' => $branch_id])->groupBy('product_id')->orderBy(['product.item_pos_seq' => SORT_ASC])->all();

$sql = "SELECT id FROM product where status =1 order by item_pos_seq ASC";

$modelx = \Yii::$app->db->createCommand($sql)->queryAll();

if ($modelx) {
    for ($xx = 0; $xx <= count($modelx) - 1; $xx++) {
        if (!in_array($modelx[$xx]['id'], $product_header)) {
            array_push($product_header, $modelx[$xx]['id']);
        }
    }
}

$model_stdgroup = \backend\models\Stdpricegroup::find()->where(['type_id'=>1])->groupBy('seq_no')->orderBy(['seq_no' => SORT_ASC])->all();
$model_stdgroup2 = \backend\models\Stdpricegroup::find()->where(['type_id'=>2])->groupBy('seq_no')->orderBy(['seq_no' => SORT_ASC])->all();
$model_stdgroup3 = \backend\models\Stdpricegroup::find()->where(['type_id'=>3])->groupBy('seq_no')->orderBy(['seq_no' => SORT_ASC])->all();
$model_transfer_branch = \backend\models\Transferbrach::find()->where(['status'=>1])->all();


?>
    <form id="form-find" action="index.php?r=routesummarybystdgroup/index" method="post">
        <div class="row">
            <div class="col-lg-4">
                <label for="">สายส่ง</label>
                <?php
                echo \kartik\select2\Select2::widget([
                    'name' => 'route_id',
                    'data' => \yii\helpers\ArrayHelper::map(\backend\models\Deliveryroute::find()->where(['status' => 1])->all(), 'id', 'name'),
                    'value' => $route_id,
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'options' => [
                            //'onchange'=> 'form.submit()',
                    ]
                ]);
                ?>
            </div>
            <div class="col-lg-4">
                <label for="">วันที่</label>
                <?php
                echo DateRangePicker::widget([
                    'name' => 'search_date',
                    // 'value'=>'2015-10-19 12:00 AM',
                    'value' => $search_date != null ? date('Y-m-d', strtotime($search_date)) : date('Y-m-d'),
                    //    'useWithAddon'=>true,
                    'convertFormat' => true,
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'วันที่',
                        //  'onchange' => 'this.form.submit();',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'timePicker' => true,
                        'timePickerIncrement' => 1,
                        'locale' => ['format' => 'Y-m-d'],
                        'singleDatePicker' => true,
                        'showDropdowns' => true,
                        'timePicker24Hour' => true
                    ]
                ]);
                ?>
            </div>
            <div class="col-lg-4">
                <button class="btn btn-sm btn-info">ค้นหา</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-lg-12" style="text-align: center"><h3>รวมยอดประจำวัน</h3></div>
    </div>
    <div class="row">
        <div class="col-lg-12" style="text-align: center"><h3><?= $route_name ?></h3></div>
    </div>
<?php
 $total_all = 0;
 $grand_total_all = [];
?>
    <table id="table-data" style="width: 100%">
        <tr style="font-weight: bold;">
            <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= date('d/m/Y',strtotime($find_date)) ?></td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= \backend\models\Product::findCode($product_header[$y]) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;">รวมเงิน</td>
        </tr>
        <?php
        $product_header_sum1  = [];
        $total_all_cash = 0;
        $total_all_credit = 0;
        $total_grand = 0;
        $total_all_amount = 0;
        ?>
        <tr>
            <td style="text-align: center;padding: 8px;border: 1px solid grey;">เบิก</td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php
            $line_car_issue_qty = 0;
            ?>
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <?php
                $product_issue_qty = getIssuecar($route_id,$find_date,$product_header[$y]);
                $line_car_issue_qty+=$product_issue_qty;
                array_push($product_header_sum1,['product'=>$product_header[$y],'qty'=>$product_issue_qty]);
                ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?=$product_issue_qty == 0?'-': number_format($product_issue_qty, 2) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;font-weight: bold;"><?= $line_car_issue_qty == 0? '-':number_format($line_car_issue_qty, 2) ?></td>
        </tr>
        <?php foreach($model_transfer_branch as $value_branch):?>
            <tr>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?=$value_branch->name?></td>
                <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
                <?php
                $line_car_issue_qty = 0;
                ?>
                <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                    <?php
                    $product_transfer_qty = getReceiveTransfer($route_id,$find_date,$product_header[$y],$value_branch->id);
                  //  $line_car_issue_qty+=$product_issue_qty;
                    array_push($product_header_sum1,['product'=>$product_header[$y],'qty'=>$product_transfer_qty]);
                    ?>
                    <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?=$product_transfer_qty == 0?'-': number_format($product_transfer_qty, 2) ?></td>
                <?php endfor; ?>
                <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;font-weight: bold;"><?= 1 == 0? '-':number_format(0, 2) ?></td>
            </tr>
        <?php endforeach;?>
        <tr style="background-color: lightgreen">
            <td style="text-align: center;padding: 8px;border: 1px solid grey;font-weight: bold;">รวมเบิก</td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <?php
                $sum_issue = 0;
                for($x=0;$x<=count($product_header_sum1)-1;$x++){
                    if($product_header_sum1[$x]['product'] == $product_header[$y]){
                        $sum_issue += (float)$product_header_sum1[$x]['qty'];
                    }
                }
                ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $sum_issue == 0? '-': number_format($sum_issue, 2) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><?= 1>0? '-':number_format(0, 2) ?></td>
        </tr>
        <tr style="background-color: #b9ca4a">
            <td style="text-align: center;padding: 8px;border: 1px solid grey;">คืน</td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <?php
                     $product_return_qty = getReturnCar($route_id,$product_header[$y],$find_date);
                ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $product_return_qty==0? '-': number_format($product_return_qty, 2) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><?= 1>0? '-':number_format(0, 2) ?></td>
        </tr>

        <?php
        $product_header_sum  = [];
        ?>
        <?php foreach ($model_stdgroup as $value_group): ?>
            <tr>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $value_group->name; ?></td>
                <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
                <?php $price_group_line_qty = 0;?>
                <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                    <?php
                    $line_product_price_qty = getQtyByPrice($route_id,$value_group->price,$value_group->type_id,$find_date,$product_header[$y]);
                    $price_group_line_qty += $line_product_price_qty;
                    array_push($product_header_sum,['product'=>$product_header[$y],'qty'=>$line_product_price_qty]);
                    array_push($grand_total_all,['product'=>$product_header[$y],'qty'=>$line_product_price_qty]);
                    $total_all_cash += ($line_product_price_qty * $value_group->price);

                    ?>
                    <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $line_product_price_qty == 0? '-':number_format($line_product_price_qty, 2) ?></td>
                <?php endfor; ?>
                <?php
                $total_all_amount += ($price_group_line_qty * $value_group->price);
                ?>
                <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><?= ($price_group_line_qty * $value_group->price) ==0? '-':number_format(($price_group_line_qty * $value_group->price), 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: pink">
            <td style="text-align: center;padding: 8px;border: 1px solid grey;font-weight: bold;">รวมขายสด</td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <?php
                    $sum_cash = 0;
                    for($x=0;$x<=count($product_header_sum)-1;$x++){
                        if($product_header_sum[$x]['product'] == $product_header[$y]){
                            $sum_cash += (float)$product_header_sum[$x]['qty'];
                        }
                    }
                ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $sum_cash == 0? '-': number_format($sum_cash, 2) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><?= $total_all_cash ==0? '-':number_format($total_all_cash, 2) ?></td>
        </tr>
        <?php $product_header_sum2  = [];?>
        <?php foreach ($model_stdgroup2 as $value_group): ?>
            <tr>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $value_group->name; ?></td>
                <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
                <?php $price_group_line_qty = 0;?>
                <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                    <?php
                    $line_product_price_qty = getQtyByPrice($route_id,$value_group->price,$value_group->type_id,$find_date,$product_header[$y]);
                    $price_group_line_qty += $line_product_price_qty;
                    array_push($product_header_sum2,['product'=>$product_header[$y],'qty'=>$line_product_price_qty]);
                    array_push($grand_total_all,['product'=>$product_header[$y],'qty'=>$line_product_price_qty]);
                    $total_all_credit += ($line_product_price_qty * $value_group->price);
                    ?>
                    <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $line_product_price_qty == 0? '-':number_format($line_product_price_qty, 2) ?></td>
                <?php endfor; ?>
                <?php
                $total_all_amount += ($price_group_line_qty * $value_group->price);
                ?>
                <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><b><?= ($price_group_line_qty * $value_group->price) ==0? '-':number_format(($price_group_line_qty * $value_group->price), 2) ?></b></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: lightcoral">
            <td style="text-align: center;padding: 8px;border: 1px solid grey;font-weight: bold;">รวมขายเชื่อ</td>
            <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
            <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                <?php
                $sum_cash2 = 0;
                for($x=0;$x<=count($product_header_sum2)-1;$x++){
                    if($product_header_sum2[$x]['product'] == $product_header[$y]){
                        $sum_cash2 += (float)$product_header_sum2[$x]['qty'];
                    }
                }
                ?>
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $sum_cash2 == 0? '-': number_format($sum_cash2, 2) ?></td>
            <?php endfor; ?>
            <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><b><?= $total_all_credit == 0? '-':number_format($total_all_credit, 2) ?></b></td>
        </tr>
        <?php foreach ($model_stdgroup3 as $value_group): ?>
            <tr style="background-color: orange">
                <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $value_group->name; ?></td>
                <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
                <?php $price_group_line_qty = 0;?>
                <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                    <?php
                    $line_product_price_qty = getQtyByPrice($route_id,$value_group->price,$value_group->type_id,$find_date,$product_header[$y]);
                    $price_group_line_qty += $line_product_price_qty;
                    ?>
                    <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?= $line_product_price_qty == 0? '-':number_format($line_product_price_qty, 2) ?></td>
                <?php endfor; ?>
                <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><b><?= $price_group_line_qty ==0? '-':number_format($price_group_line_qty, 2) ?></b></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: skyblue">
             <td style="text-align: center;padding: 8px;border: 1px solid grey;"><b>รวมขายทั้งหมด</b></td>
             <!--            <td style="text-align: center;padding: 0px;border: 1px solid grey">จำนวน</td>-->
             <?php for ($y = 0; $y <= count($product_header) - 1; $y++): ?>
                 <?php
                 $sum_total = 0;
                 for($x=0;$x<=count($grand_total_all)-1;$x++){
                     if($grand_total_all[$x]['product'] == $product_header[$y]){
                         $sum_total += (float)$grand_total_all[$x]['qty'];
                     }
                 }
                 ?>
                 <td style="text-align: center;padding: 8px;border: 1px solid grey;"><?=$sum_total==0?'-':number_format($sum_total,2)?></td>
             <?php endfor; ?>
             <td style="text-align: right;padding: 8px;border: 1px solid grey;background-color: skyblue;"><b><?= $total_all_amount == 0? '-':number_format($total_all_amount, 2) ?></b></td>
         </tr>
    </table>


<?php
function getIssuecarOld($route_id, $product_id, $order_date)
{
    $issue_qty = 0;

    if($route_id > 0){
        $sql = "SELECT SUM(t2.origin_qty) as qty";
        $sql .= " FROM journal_issue as t1 INNER JOIN journal_issue_line as t2 ON t2.issue_id = t1.id";
        $sql .= " WHERE t2.product_id =" . $product_id;
        $sql .= " AND not isnull(t1.delivery_route_id)";
        $sql .= " AND t1.status in (2)";
        $sql .= " AND date(t1.trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        if ($route_id != null) {
            $sql .= " AND t1.delivery_route_id=" . $route_id;
        }
        $sql .= " GROUP BY t2.product_id";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $issue_qty = $model[$i]['qty'];
            }
        }
    }

    return $issue_qty;
}

function getQtyByPrice($route_id, $price,$sale_type, $order_date,$product_id)
{
    $sale_qty = 0;

    if($route_id >0){
        $sql = "SELECT SUM(t2.qty) as qty";
        $sql .= " FROM orders as t1 INNER JOIN order_line as t2 ON t2.order_id = t1.id";
        $sql .= " WHERE t2.product_id =" . $product_id;
        // $sql .= " AND not isnull(t1.delivery_route_id)";
        $sql .= " AND t1.status <> 3";
        $sql .= " AND t2.status not in (3,500)";
        $sql .= " AND t2.price = ".$price;
        $sql .= " AND t2.sale_payment_method_id = ".$sale_type;
        $sql .= " AND date(t1.order_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        if ($route_id != null) {
            $sql .= " AND t1.order_channel_id=" . $route_id;
        }
        $sql .= " GROUP BY t2.price";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $sale_qty = $model[$i]['qty'];
            }
        }
    }


    return $sale_qty;
}


function getReceiveTransfer($route_id,$order_date,$product_id,$transfer_from_id)
{
    $sale_qty = 0;

    if($route_id >0){
        $sql = "SELECT SUM(qty) as qty";
        $sql .= " FROM query_issue_from_transfer";
        $sql .= " WHERE product_id =" . $product_id;
        // $sql .= " AND not isnull(t1.delivery_route_id)";
        $sql .= " AND date(trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        if ($route_id != null) {
            $sql .= " AND delivery_route_id=" . $route_id;
        }
        if ($transfer_from_id != null) {
            $sql .= " AND transfer_branch_id=" . $transfer_from_id;
        }
        $sql .= " GROUP BY product_id";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $sale_qty = $model[$i]['qty'];
            }
        }
    }


    return $sale_qty;
}
function getIssuecar($route_id,$order_date,$product_id)
{
    $sale_qty = 0;

    if($route_id >0){
        $sql = "SELECT SUM(qty) as qty";
        $sql .= " FROM query_issue_none_transfer";
        $sql .= " WHERE product_id =" . $product_id;
        // $sql .= " AND not isnull(t1.delivery_route_id)";
        $sql .= " AND date(trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        if ($route_id != null) {
            $sql .= " AND delivery_route_id=" . $route_id;
        }

        $sql .= " GROUP BY product_id";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $sale_qty = $model[$i]['qty'];
            }
        }
    }


    return $sale_qty;
}
function getReturnCar($route_id, $product_id, $order_date)
{
    $issue_qty = 0;

//    $sql = "SELECT SUM(t1.qty) as qty";
//    $sql .= " FROM stock_trans as t1 INNER JOIN orders as t2 ON t1.trans_ref_id=t2.id";
//    $sql .= " WHERE  t1.product_id =" . $product_id;
//    $sql .= " AND t1.activity_type_id=7";
//    $sql .= " AND date(t1.trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
//    if ($route_id != null) {
//        $sql .= " AND t2.order_channel_id=" . $route_id;
//    }
    $sql = "SELECT SUM(t1.qty) as qty";
    $sql .= " FROM stock_trans as t1 ";
    $sql .= " WHERE  t1.product_id =" . $product_id;
    $sql .= " AND t1.activity_type_id in (7,26)";
    $sql .= " AND date(t1.trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
    if ($route_id != null) {
        $sql .= " AND t1.trans_ref_id=" . $route_id;
    }

    $sql .= " GROUP BY t1.product_id";
    $query = \Yii::$app->db->createCommand($sql);
    $model = $query->queryAll();
    if ($model) {
        for ($i = 0; $i <= count($model) - 1; $i++) {
            $issue_qty = $model[$i]['qty'];
        }
    }
    return $issue_qty;
}
function getTransferTotalAmount($route_id, $order_date)
{
    $sale_qty = 0;

    if($route_id >0){
        $sql = "SELECT SUM(t2.qty) as qty";
        $sql .= " FROM orders as t1 INNER JOIN order_line as t2 ON t2.order_id = t1.id";
        $sql .= " WHERE t2.product_id =" . $product_id;
        // $sql .= " AND not isnull(t1.delivery_route_id)";
        $sql .= " AND t1.status <> 3";
        $sql .= " AND t2.status not in (3,500)";
        $sql .= " AND t2.price = ".$price;
        $sql .= " AND t2.sale_payment_method_id = ".$sale_type;
        $sql .= " AND date(t1.order_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        if ($route_id != null) {
            $sql .= " AND t1.order_channel_id=" . $route_id;
        }
        $sql .= " GROUP BY t2.price";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $sale_qty = $model[$i]['qty'];
            }
        }
    }


    return $sale_qty;
}

?>