<?php

namespace App\Http\Controllers;

use App\Models\Params;
use Illuminate\Http\Request;

class ParamsController extends Controller
{
    public function edit()
    {
        $params = Params::first() ?? new Params(['unit_price' => 0, 'valve_per_day' => 0]);
        return view('params.edit', compact('params'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'unit_price'    => 'required|numeric|min:0',
            'valve_per_day' => 'required|integer|min:0',
        ]);

        $params = Params::find($id);
        if ($params) {
            $params->update($data);
        } else {
            Params::create($data);
        }

        return redirect()->route('params.edit')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
