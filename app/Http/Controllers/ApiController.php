<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Book;
use App\User;
use App\RentedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;


class ApiController extends Controller
{
    public function register(Request $request){
        $validator  = Validator::make($request->all(),[
            'firstName' => ['required', 'alpha'],
            'lastName' => ['required','alpha'],
            'email'  => ['required','unique:users', 'email'],
            'password' => ['required', 'min:14'],
            'mobile' => ['required', 'unique:users', 'min:10'],
            'age' => 'required|numeric',
            'gender' => 'required| in:m,f,o',
            'city' => 'required',
        ]);

        if ($validator ->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }else{

            $user = User::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'mobile' => $request->mobile,
                'age' => $request->age,
                'gender' => $request->gender,
                'city' => $request->city
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ], Response::HTTP_OK);
        }

    }   

    public function login(Request $request){
        $validator  = Validator::make($request->all(),[
            'email'  => 'required',
            'password' => 'required',
        ]);

        if ($validator ->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }else{
          try{
            $email = $request->email;
            $password = $request->password;
            $chckUser = User::where('email',$email)->first();
            if(Hash::check($password, $chckUser->password)){
                //create token
                $token =  JWTAuth::fromUser($chckUser);
                return response()->json([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'data' => [
                        'token' => $token,
                        'user' => $chckUser
                    ]
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Check your email & password'
                ], 404);
            }
          }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
          }
        }
    }

    public function getUser(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            if($token){
                return response()->json([
                    'success' => true,
                    'message' => 'Profile retrieved successfully',
                    'data' => [
                        'user' => $token
                    ]
                ], 200);
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Fail to retrieve',
                ], 200);
            }
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function editUser(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();

            $validator  = Validator::make($request->all(),[
                'firstName' => ['required', 'alpha'],
                'lastName' => ['required','alpha'],
                'city' => 'required',
            ]);
    
            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                $getUser = User::find($token->id);
                $getUser->firstName = $request->firstName;
                $getUser->lastName = $request->lastName;
                $getUser->city = $request->city;
                if($getUser->save()){
                    return response()->json([
                        'success' => true,
                        'message' => 'Profile edited successfully',
                        'data' => [
                            'user' => $getUser
                        ]
                    ], 200);
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => 'Fail to edit profile',
                    ], 200);
                }
            }
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function removeUser(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $user = User::find($token->id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'User removed successfully',
            ], 200);
           
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function removeBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();

            $validator  = Validator::make($request->all(),[
                'bookId' => 'numeric',
            ]);
    
            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                $book = Book::find($request->bookId);
                if($book){
                    $book->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Book removed successfully',
                    ], 200);
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => 'check book id',
                    ], 200);
                }
            }
           
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function editBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $validator  = Validator::make($request->all(),[
                'id' => 'required',
                'name' => 'required',
                'author' =>  'required'
            ]);
    
            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                $book = Book::find($request->id);
                if($book){
                    $book->book_name = $request->name;
                    $book->author = $request->author;
                    $book->save();
                    return response()->json([
                        'success' => true,
                        'message' => 'Book updated successfully',
                        'data' => $book
                    ], 200);
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => 'book not found',
                    ], 200);
                }
            }
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function allBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $book = Book::all();
            if($book){
                return response()->json([
                    'success' => true,
                    'message' => 'Book retrieved successfully',
                    'data' => [
                        'user' => $book
                    ]
                ], 200);
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Fail to retrieve',
                ], 200);
            }
        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function createBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $validator  = Validator::make($request->all(),[
                'book_name' => ['required'],
                'author' => ['required'],
                'cover_image'  => ['required','mimes:jpeg,bmp,png'],
            ]);

            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                $book = Book::create([
                    'book_name' => $request->book_name,
                    'author' => $request->author,
                    'cover_image' => $request->file('cover_image')->storeAs('public', $request->book_name."-".$request->book_name."".time().".".$request->cover_image->extension())
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Book created successfully'
                ], Response::HTTP_OK);
            }

        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }

    }
    
    public function rentBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $validator  = Validator::make($request->all(),[
                'bookId' => 'required',
            ]);

            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                if(Book::find($request->bookId)){
                    $rbook = RentedBook::create([
                        'userID' => $token->id,
                        'bookID' => $request->bookId,
                        'bookStatus' => 'rented' 
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Book rented successfully'
                    ], Response::HTTP_OK);
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => 'Check book id'
                    ], Response::HTTP_OK);
                }
            }

        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }

    }

    public function returnBook(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();
            $validator  = Validator::make($request->all(),[
                'bookId' => 'required',
            ]);

            if ($validator ->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }else{
                if(Book::find($request->bookId)){
                    $rbook = RentedBook::where('bookID',$request->bookId)->first();
                    if($rbook){
                        $rbook->bookStatus = 'return';
                        $rbook->save();
                        return response()->json([
                            'success' => true,
                            'message' => 'Book returned successfully'
                        ], Response::HTTP_OK);
                    }else{
                        return response()->json([
                            'success' => true,
                            'message' => 'Book not exists to return'
                        ], Response::HTTP_OK);
                    }
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => 'Check book id'
                    ], Response::HTTP_OK);
                }
            }

        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }

    }

    public function bookStatus(Request $request){
        try{
            $token = JWTAuth::parseToken()->authenticate();

            $rentBook = DB::select('SELECT R.id as id, 
            U.firstName as firstName, 
            U.lastName as lastName,
            B.author as author, 
            B.book_name as bookName,
            R.bookStatus as BookStatus
            FROM 
            rentedBooks R 
            JOIN users U
            ON U.id = R.userId 
            JOIN books B
            ON b.id = R.bookID ORDER BY bookStatus ASC');
            return response()->json([
                'success' => true,
                'message' => 'All books retrieved successfully',
                'data' => $rentBook
            ], Response::HTTP_OK);

        }catch(JWtException $e){
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }

    }
}
