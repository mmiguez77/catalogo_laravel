<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //obtener listado de marcas
        $marcas = Marca::paginate(5);
        return view('adminMarcas', ['marcas' => $marcas]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('agregarMarca');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Tomar datos del FORM
        $mkNombre = $request->mkNombre;
        
        // Validar datos
        $this->validarForm($request);
        
        // Guardar en DB
        // -- Instanciamos objeto
        $Marca = new Marca;
        // -- Asignamos atributos
        $Marca->mkNombre = $mkNombre;
        // Insert
        $Marca->save();
        // Redireccion
        return redirect('/adminMarcas')
            ->with(['mensaje' => 'Marca: ' .$mkNombre. ' agregada correctamente']);
    }

    private function validarForm( Request $request ) : void
    {
        $request->validate(
        [
            'mkNombre'=>'required|min:2|max:50',
        ], 
        [
            'mkNombre.required'=>'Campo Obligatorio',
            'mkNombre.min'=>'Minimo 2 Caracteres',
            'mkNombre.max'=>'Maximo 50 Caracteres',
        ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Marca = Marca::find($id);

        return view('modificarMarca', [ 'Marca' => $Marca]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // validation
        $this->validarForm($request);

        // obtener datos de una marca por ID
        $Marca = Marca::find($request->idMarca);

        // modificar atributos 
        $Marca->mkNombre = $request->mkNombre;

        // guardar
        $Marca->save();

        // redireccion
        return redirect('/adminMarcas')
            ->with(['mensaje' => 'Marca: ' .$request->mkNombre. ' agregada correctamente']);
    }

    private function productoPorMarca($idMarca)
    {
        $check = Producto::where('idMarca', $idMarca)->first();
        return $check;
    } 


    public function confirmarBaja($id) 
    {   
        // obtener datos de una marca por ID
        $Marca = Marca::find($id);
        
        // caso que no tenga productos la marca se puede borrar
        if ( !$this->productoPorMarca($id) ) {
            return view('/eliminarMarca', [ 'Marca' => $Marca]);
        }
        
        // redireccion caso que no se pueda borrar porque tiene productos la marca
        return redirect('/adminMarcas') 
            ->with([ 
                'mensaje' => 'La marca ' . $Marca->mkNombre . ' no se puede eliminar', 
                'alert' => 'danger' 
            ]); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Marca::destroy($request->idMarca);

        return redirect('/adminMarcas')
            ->with([
                    'mensaje' => 'Marca: ' .$request->mkNombre. ' eliminada correctamente',
                    'alert' => 'info'
            ]);

    }
}
