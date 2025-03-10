<?php

namespace App\Http\Controllers;

use App\Models\Manifest;
use App\Models\Client;
use App\Models\Agent;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ManifestController extends Controller
{
    public function createManifestFormData()
    {
        $from = ShippingRate::distinct()->pluck('origin');
        $to = ShippingRate::distinct()->pluck('destination');

        // 只获取非 admin 的公司
        $companies = Client::where('role', '!=', 'admin')
            ->select(["id", "company_name"])
            ->get();

        return response()->json([
            "companies" => $companies,
            "from" => $from,
            "to" => $to
        ]);
    }


    public function index()
    {
        $manifests = Manifest::with(['consignor'])->get();
        return response()->json($manifests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'consignor' => 'required',
            'consignee' => 'required|string', // 这里 consignee 变成 string
            'cn_no' => 'required|integer',
            'pcs' => 'required|integer',
            'kg' => 'required|integer',
            'gram' => 'required|integer',
            'remarks' => 'nullable|string',
            'date' => 'required|date',
            'awb_no' => 'required|integer',
            'to' => 'required|string',
            'from' => 'required|string',
            'flt' => 'required|string',
            'manifest_no' => 'required|integer',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $consignor = is_numeric($request->input('consignor'))
            ? Client::find($request->input('consignor'))
            : Client::firstOrCreate(['name' => $request->input('consignor')]);

        if (!$consignor) {
            return response()->json(['error' => 'Consignor not found'], 400);
        }

        $kg = $request->kg;
        $gram = $request->gram;
        $origin = $request->from;
        $destination = $request->to;
        $shippingRate = ShippingRate::where('origin', $origin)->where('destination', $destination)->first();

        if (!$shippingRate) {
            return response()->json(['error' => 'Shipping rate not found for this route'], 400);
        }

        $total_weight = $kg + ($gram / 1000);
        if ($total_weight <= $shippingRate->minimum_weight) {
            $total_price = $shippingRate->minimum_price;
        } else {
            $extra_kg = $total_weight - $shippingRate->minimum_weight;
            $total_price = $shippingRate->minimum_price + ($extra_kg * $shippingRate->additional_price_per_kg);
        }

        $discount = $request->discount ?? 0;
        $total_price_after_discount = $total_price * (1 - ($discount / 100));

        $manifest = Manifest::create([
            'origin' => $request->input('origin'),
            'consignor_id' => $consignor->id,
            'consignee_name' => $request->input('consignee'), // 直接存字符串
            'cn_no' => $request->input('cn_no'),
            'pcs' => $request->input('pcs'),
            'kg' => $request->input('kg'),
            'gram' => $request->input('gram'),
            'remarks' => $request->input('remarks'),
            'date' => $request->input('date'),
            'awb_no' => $request->input('awb_no'),
            'to' => $request->input('to'),
            'from' => $request->input('from'),
            'flt' => $request->input('flt'),
            'manifest_no' => $request->input('manifest_no'),
            'total_price' => $total_price_after_discount,
            'discount' => $discount,
            'delivery_date' => null,
        ]);

        return response()->json($manifest->load('consignor'), 201);
    }


    public function confirmShipment($id, Request $request)
    {
        $manifest = Manifest::findOrFail($id);

        if ($manifest->delivery_date) {
            return response()->json(['error' => 'Shipment already confirmed'], 400);
        }

        $deliveryDate = $request->input('delivery_date') ?: Carbon::now()->toDateString();
        $manifest->update(['delivery_date' => $deliveryDate]);

        return response()->json([
            'message' => 'Shipment confirmed successfully',
            'manifest' => $manifest
        ]);
    }

    public function show($id)
    {
        $manifest = Manifest::with(['consignor'])->findOrFail($id);
        return response()->json($manifest);
    }

    public function update(Request $request, Manifest $manifest)
    {
        $request->validate([
            'origin' => 'sometimes|string',
            'consignor' => 'sometimes',
            'consignee' => 'sometimes|string',
            'cn_no' => 'sometimes|integer',
            'pcs' => 'sometimes|integer',
            'kg' => 'sometimes|integer',
            'gram' => 'sometimes|integer',
            'remarks' => 'nullable|string',
            'date' => 'sometimes|date',
            'awb_no' => 'sometimes|integer',
            'to' => 'sometimes|string',
            'from' => 'sometimes|string',
            'flt' => 'sometimes|string',
            'manifest_no' => 'sometimes|integer',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);
    
        $manifest_data = [
            'manifest_number' => $manifest->manifest_number,
        ];
    
        // // 处理 Consignor
        // if ($request->has('consignor')) {
        //     $consignor = is_numeric($request->input('consignor'))
        //         ? Client::find($request->input('consignor'))
        //         : Client::firstOrCreate(['name' => $request->input('consignor')]);
    
        //     if ($consignor) {
        //         $manifest->consignor_id = $consignor->id;
        //     } else {
        //         return response()->json(['error' => 'Invalid consignor'], 400);
        //     }
        // }
    
        // 处理 Consignee 和其他字段
        if ($request->has('consignee')) {
            $manifest_data['consignee'] = $request->consignee;
        }
        if ($request->has('origin')) {
            $manifest_data['origin'] = $request->origin;
        }
        if ($request->has('from')) {
            $manifest_data['from'] = $request->from;
        }
        if ($request->has('to')) {
            $manifest_data['to'] = $request->to;
        }

        if ($request->has('cn_no')) {
            $manifest_data['cn_no'] = $request->cn_no;
        }
        if ($request->has('pcs')) {
            $manifest_data['pcs'] = $request->pcs;
        }
        if ($request->has('kg')) {
            $manifest_data['kg'] = $request->kg;
        }
        if ($request->has('gram')) {
            $manifest_data['gram'] = $request->gram;
        }
        if ($request->has('remarks')) {
            $manifest_data['remarks'] = $request->remarks;
        }
        if ($request->has('date')) {
            $manifest_data['date'] = $request->date;
        }
        if ($request->has('awb_no')) {
            $manifest_data['awb_no'] = $request->awb_no;
        }
        if ($request->has('flt')) {
            $manifest_data['flt'] = $request->flt;
        }
        if ($request->has('manifest_no')) {
            $manifest_data['manifest_no'] = $request->manifest_no;
        }
        

    
        // 计算新价格
        if ($request->hasAny(['kg', 'gram', 'discount', 'from', 'to'])) {
            $kg = $request->input('kg', $manifest->kg);
            $gram = $request->input('gram', $manifest->gram);
            $discount = $request->input('discount', $manifest->discount);
            $origin = $request->input('from', $manifest->from);
            $destination = $request->input('to', $manifest->to);
            
    
            $shippingRate = ShippingRate::where('origin', $origin)
                ->where('destination', $destination)
                ->first();
    
            if ($shippingRate) {
                $total_weight = $kg + ($gram / 1000);
                if ($total_weight <= $shippingRate->minimum_weight) {
                    $total_price = $shippingRate->minimum_price;
                } else {
                    $extra_kg = $total_weight - $shippingRate->minimum_weight;
                    $total_price = $shippingRate->minimum_price + ($extra_kg * $shippingRate->additional_price_per_kg);
                }
    
                $total_price_after_discount = $total_price * (1 - ($discount / 100));
                $manifest_data['total_price'] = $total_price_after_discount;
                $manifest_data['discount'] = $discount;
            }
        }
    
        // 批量更新字段
        $manifest->fill($manifest_data);
        $manifest->save();
    
        return response()->json($manifest->load('consignor'));
    }


    public function destroy($id)
    {
        Manifest::destroy($id);
        return response()->json(null, 204);
    }
}