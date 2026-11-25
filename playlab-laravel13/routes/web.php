<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn()=>view('home'))->name('home');
Route::get('/salas-publicas',[RoomController::class,'index'])->name('rooms.index');
Route::post('/salas/unirse',[RoomController::class,'join'])->middleware('throttle:20,1')->name('rooms.join');
Route::get('/salas/{code}',[RoomController::class,'show'])->where('code','[A-Za-z0-9]{6}')->name('rooms.show');
Route::get('/salas/{code}/estado',[RoomController::class,'state'])->middleware('throttle:120,1')->name('rooms.state');
Route::post('/salas/{code}/responder',[RoomController::class,'answer'])->middleware('throttle:30,1')->name('rooms.answer');

Route::middleware('guest')->group(function(){
    Route::get('/ingresar',[AuthController::class,'loginForm'])->name('login');
    Route::post('/ingresar',[AuthController::class,'login'])->middleware('throttle:8,1');
    Route::get('/registro',[AuthController::class,'registerForm'])->name('register');
    Route::post('/registro',[AuthController::class,'register'])->middleware('throttle:5,1');
    Route::get('/recuperar-contrasena',[AuthController::class,'forgotForm'])->name('password.request');
    Route::post('/recuperar-contrasena',[AuthController::class,'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}',[AuthController::class,'resetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena',[AuthController::class,'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function(){
    Route::post('/salir',[AuthController::class,'logout'])->name('logout');
    Route::get('/panel',DashboardController::class)->name('dashboard');
    Route::get('/cuestionarios',[QuizController::class,'index'])->name('quizzes.index');
    Route::get('/cuestionarios/crear',[QuizController::class,'create'])->name('quizzes.create');
    Route::post('/cuestionarios',[QuizController::class,'store'])->name('quizzes.store');
    Route::get('/cuestionarios/{quiz}/editar',[QuizController::class,'edit'])->name('quizzes.edit');
    Route::put('/cuestionarios/{quiz}',[QuizController::class,'update'])->name('quizzes.update');
    Route::delete('/cuestionarios/{quiz}',[QuizController::class,'destroy'])->name('quizzes.destroy');
    Route::get('/cuestionarios/{quiz}/preguntas/crear',[QuestionController::class,'create'])->name('questions.create');
    Route::post('/cuestionarios/{quiz}/preguntas',[QuestionController::class,'store'])->name('questions.store');
    Route::get('/preguntas/{question}/editar',[QuestionController::class,'edit'])->name('questions.edit');
    Route::put('/preguntas/{question}',[QuestionController::class,'update'])->name('questions.update');
    Route::delete('/preguntas/{question}',[QuestionController::class,'destroy'])->name('questions.destroy');
    Route::get('/salas/crear',[RoomController::class,'create'])->name('rooms.create');
    Route::post('/salas',[RoomController::class,'store'])->name('rooms.store');
    Route::post('/salas/{code}/iniciar',[RoomController::class,'start'])->name('rooms.start');
    Route::post('/salas/{code}/revelar',[RoomController::class,'reveal'])->name('rooms.reveal');
    Route::post('/salas/{code}/siguiente',[RoomController::class,'next'])->name('rooms.next');
    Route::post('/salas/{code}/finalizar',[RoomController::class,'finish'])->name('rooms.finish');
});
