<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\admin\Category;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\LogStock;
use App\Models\Menu;
use App\Models\MenuStock;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\Promotion;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Student;

class Main extends Controller
{
    public function index(Request $request)
    {
        $table_id = $request->input('table');
        if ($table_id) {
            session(['table_id' => $table_id]);
        }
        $promotion = Promotion::where('is_status', 1)->get();
        $category = Categories::has('menu')->with('files')->get();
        return view('users.main_page', compact('category', 'promotion'));
    }

    public function detail($id)
    {
        $menu = Menu::where('categories_id', $id)->with('files', 'option')->orderBy('created_at', 'asc')->get();
        return view('users.detail_page', compact('menu'));
    }

    // public function order()
    // {
    //     return view('users.list_page');
    // }

    public function order(Request $request)
    {
        $search = $request->input('search');

        $students = Student::query()
            ->when($search, function ($query, $search) {
                return $query->where('student_id', 'like', "%{$search}%");
            })
            ->get();

        return view('users.list_page', compact('students', 'search'));
    }

    // public function SendOrder(Request $request)
    // {
    //     $data = [
    //         'status' => false,
    //         'message' => 'สั่งออเดอร์ไม่สำเร็จ',
    //     ];
    //     $orderData = $request->input('orderData');
    //     $remark = $request->input('remark');
    //     $item = array();
    //     $total = 0;
    //     foreach ($orderData as $order) {
    //         foreach ($order as $rs) {
    //             $item[] = [
    //                 'id' => $rs['id'],
    //                 'price' => $rs['price'],
    //                 'option' => $rs['option'],
    //                 'qty' => $rs['qty'],
    //             ];
    //             $total = $total + ($rs['price'] * $rs['qty']);
    //         }
    //     }

    //     if (!empty($item)) {
    //         $order = new Orders();
    //         $order->table_id = session('table_id') ?? '1';
    //         $order->total = $total;
    //         $order->remark = $remark;
    //         $order->status = 1;
    //         if ($order->save()) {
    //             foreach ($item as $rs) {
    //                 $orderdetail = new OrdersDetails();
    //                 $orderdetail->order_id = $order->id;
    //                 $orderdetail->menu_id = $rs['id'];
    //                 $orderdetail->option_id = $rs['option'];
    //                 $orderdetail->quantity = $rs['qty'];
    //                 $orderdetail->price = $rs['price'];
    //                 if ($orderdetail->save()) {
    //                     $menuStock = MenuStock::where('menu_option_id', $rs['option'])->get();
    //                     foreach ($menuStock as $stock_rs) {
    //                         $stock = Stock::find($stock_rs->stock_id);
    //                         $stock->amount = $stock->amount - ($stock_rs->amount * $rs['qty']);
    //                         if ($stock->save()) {
    //                             $log_stock = new LogStock();
    //                             $log_stock->stock_id = $stock_rs->stock_id;
    //                             $log_stock->order_id = $order->id;
    //                             $log_stock->menu_option_id = $rs['option'];
    //                             $log_stock->old_amount = $stock_rs->amount;
    //                             $log_stock->amount = ($stock_rs->amount * $rs['qty']);
    //                             $log_stock->status = 2;
    //                             $log_stock->save();
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         event(new OrderCreated(['📦 มีออเดอร์ใหม่']));
    //         $data = [
    //             'status' => true,
    //             'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
    //         ];
    //     }
    //     return response()->json($data);
    // }

    public function SendOrder(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'สั่งออเดอร์ไม่สำเร็จ',
        ];

        $orderData = $request->input('orderData');
        $remark = $request->input('remark');
        $studentId = $request->input('student_id');

        $student = Student::find($studentId);
        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบนักเรียนที่ระบุ'
            ]);
        }

        $items = [];
        $total = 0;
        foreach ($orderData as $order) {
            foreach ($order as $rs) {
                $items[] = [
                    'id' => $rs['id'],
                    'price' => $rs['price'],
                    'option' => $rs['option'],
                    'qty' => $rs['qty'],
                ];
                $total += $rs['price'] * $rs['qty'];
            }
        }

        if (!empty($items)) {
            $order = new Orders();
            $order->table_id = session('table_id') ?? '1';
            $order->student_id = $studentId; // เพิ่มบันทึก student_id
            $order->total = $total;
            $order->remark = $remark;
            $order->status = 2;

            if ($order->save()) {
                foreach ($items as $rs) {
                    $orderdetail = new OrdersDetails();
                    $orderdetail->order_id = $order->id;
                    $orderdetail->menu_id = $rs['id'];
                    $orderdetail->option_id = $rs['option'];
                    $orderdetail->quantity = $rs['qty'];
                    $orderdetail->price = $rs['price'];
                    if ($orderdetail->save()) {
                        $menuStock = MenuStock::where('menu_option_id', $rs['option'])->get();
                        foreach ($menuStock as $stock_rs) {
                            $stock = Stock::find($stock_rs->stock_id);
                            $stock->amount -= ($stock_rs->amount * $rs['qty']);
                            if ($stock->save()) {
                                $log_stock = new LogStock();
                                $log_stock->stock_id = $stock_rs->stock_id;
                                $log_stock->order_id = $order->id;
                                $log_stock->menu_option_id = $rs['option'];
                                $log_stock->old_amount = $stock_rs->amount;
                                $log_stock->amount = ($stock_rs->amount * $rs['qty']);
                                $log_stock->status = 2;
                                $log_stock->save();
                            }
                        }
                    }
                }
            }

            event(new OrderCreated(['📦 มีออเดอร์ใหม่']));

            $data = [
                'status' => true,
                'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
            ];
        }

        return response()->json($data);
    }


    public function sendEmp()
    {
        event(new OrderCreated(['ลูกค้าเรียกจากโต้ะที่ ' . session('table_id')]));
    }
}
