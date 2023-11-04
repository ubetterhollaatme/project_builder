<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Alert;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * @throws \Exception
     */
    public function create(Request $request): Node
    {
        $request->validate([
            'name' => 'required|min:3',
            'desc' => 'required|min:5',
            'email' => 'required|unique:data_producer_nodes|email',
            'phone' => 'required',
        ]);

        $dpn = new Node([
            'name' => $request->post('name'),
            'desc' => $request->post('desc'),
            'email' => $request->post('email'),
            'phone' => $request->post('phone'),
        ]);

        try {
            $dpn->save();
        } catch (\Throwable $e) {
            throw new \Exception($e);
        }

        return $dpn;
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Node $dataProducerNode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Node $dataProducerNode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Node $dataProducerNode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Node $dataProducerNode)
    {
        //
    }
}
