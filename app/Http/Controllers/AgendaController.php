<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{


    public function __construct()
    {
//        $this->middleware('api:auth');
    }


    public function index()
    {
        return Agenda::paginate(20);
    }

    public function show($id)
    {
        return Agenda::findOrFail($id);
    }

    public function store(Request $request)
    {
        $agenda = Agenda::create($request->all());
        return $agenda;
    }

    public function update(Request $request, $id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->update($request->all());

        return $agenda;
    }

    public function destroy($id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->delete();
        return '';
    }




}
