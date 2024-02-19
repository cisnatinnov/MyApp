<?php

namespace App\Http\Controllers;

use App\Models\Mata_kuliah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
  /**
 * Create a new controller instance.
 *
 * @return void
 */
  public function __construct()
  {
    //
  }

  public function index()
  {
    return response()->json([
      'message' => 'Welcome to API Mahasiswa'
    ]);
  }

  /**
   * Display a listing of the resource.
   * @param  \Illuminate\Http\Request  $request
   */
  public function lists(Request  $request)
  {
    $mahasiswa = DB::select("SELECT mahasiswas.id, mahasiswas.nama, tgl_lahir, jenis_kelamin, alamat, COUNT(mata_kuliahs.mahasiswa_id) FROM mahasiswas
    LEFT JOIN mata_kuliahs ON mata_kuliahs.mahasiswa_id = mahasiswas.id
    WHERE LOWER(mahasiswas.nama) LIKE '%".strtolower($request->query('search'))."%'
    OR LOWER(jenis_kelamin) LIKE '%".strtolower($request->query('search'))."%'
    OR LOWER(alamat) LIKE '%".strtolower($request->query('search'))."%'
    GROUP BY mahasiswas.id, mahasiswas.nama, jenis_kelamin, alamat");
    return response()->json($mahasiswa);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   */
  public function create(Request $request)
  {
    $post = $request->all();

    $request->validate([
      'nama' => 'required',
      'jenis_kelamin' => 'required',
      'alamat' => 'required',
      'sks' => 'required',
      'mata_kuliah' => 'required'
    ]);
    $mahasiswa = new Mahasiswa;
    $mahasiswa->nama = $post['nama'];
    $mahasiswa->jenis_kelamin = $post['jenis_kelamin'];
    $mahasiswa->alamat = $post['alamat'];
    $mahasiswa->sks = $post['sks'];
    $mahasiswa->save();

    foreach($post['mata_kuliah'] as $obj) {
      Mata_kuliah::create([
        "mahasiswa_id" => $mahasiswa->id,
        "nama" => $obj['nama'] 
      ]);
    }
    return response()->json('Mahasiswa created successfully.');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   */
  public function show($id)
  {
    $mahasiswa = Mahasiswa::find($id);
    $mata_kuliah = DB::table('mata_kuliahs')->where('mahasiswa_id', $id)->get();

    return response()->json(['mahasiswa' => $mahasiswa, 'mata_kuliah' => $mata_kuliah]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   */
  public function update(Request $request, $id)
  {
    $post = $request->all();

    $request->validate([
      'nama' => 'required',
      'jenis_kelamin' => 'required',
      'alamat' => 'required',
      'sks' => 'required',
      'mata_kuliah' => 'required'
    ]);
    $mahasiswa = DB::table('mahasiswas');
    $mahasiswa->where('id', $id);
    $mahasiswa->update([
      'nama' => $post['nama'],
      'jenis_kelamin' => $post['jenis_kelamin'],
      'alamat' => $post['alamat'],
      'sks' => $post['sks']
    ]);

    DB::table('mata_kuliahs')->where('mahasiswa_id', $id)->delete();
    foreach($post['mata_kuliah'] as $obj) {
      Mata_kuliah::create([
        "mahasiswa_id" => $id,
        "nama" => $obj['nama'] 
      ]);
    }
    return response()->json('Mahasiswa updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   */
  public function destroy($id)
  {
    $mata_kuliahs = DB::table('mata_kuliahs')->where('mahasiswa_id', $id);
    if ($mata_kuliahs) $mata_kuliahs->delete();
    $mahasiswa = Mahasiswa::find($id);
    $mahasiswa->delete();
    return response()->json('Mahasiswa deleted successfully');
  }
}