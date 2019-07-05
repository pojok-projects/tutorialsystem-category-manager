<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CategoryController extends Controller
{
    private $client;

    function __construct()
    {
        $this->client = new Client();
    }

    private function checkduplicate($name)
    {
        $result = $this->client->request('POST', 'https://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/search', [
            'name' => $name
        ]);
            
        if ($result->getStatusCode() != 200) {
            return [
                'response' => 500
            ];
        }else{
            $check_duplicate = json_decode($result->getBody(), true);
            if ($check_duplicate['status']['total'] == 0) {
                return [
                    'response' => false
                ];
            }else{
                return [
                    'response' => true,
                    'result' => $check_duplicate['result']
                ];
            }
        }
    }

    public function index()
    {

        $result = $this->client->request('GET', 'http://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category');

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        return response()->json(json_decode($result->getBody(), true));

    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);
        
        $check_duplicate = self::checkduplicate($request->name);

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        if ($check_duplicate['response']) {
            return response()->json([
                'status' => [
                    'code' => '409',
                    'message' => 'Duplicate category',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', 'https://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/store', [
                'name' => $request->name,
                'description' => $request->description
            ]);
            
            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => '500',
                        'message' => 'Bad Gateway',
                    ]
                ], 500);
            }

            return response()->json(json_decode($result->getBody(), true));
        }
    }
    
    public function show($id)
    {
        $result = $this->client->request('GET', 'http://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        return response()->json(json_decode($result->getBody(), true));
    }

    public function search(Request $request)
    {
        $rules = [
            'name' => 'required'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $name = $request->name;
        $result = $this->client->request('POST', 'https://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/search', [
            'name' => $name
        ]);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        $search_category = json_decode($result->getBody(), true);

        if ($search_category['status']['total']==0) {
            return response()->json([
                'status' => [
                    'code' => '404',
                    'message' => 'Category not found',
                ]
            ], 404);
        }else{
            return response()->json($search_category, 200);  
        }
 
              
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $check_duplicate = self::checkduplicate($request->name);

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        if ($check_duplicate['response']) {
            return response()->json([
                'status' => [
                    'code' => '409',
                    'message' => 'Duplicate category',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', 'https://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/update/'.$id, [
                'name' => $request->name,
                'description' => $request->description
            ]);

            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => '500',
                        'message' => 'Bad Gateway',
                    ]
                ], 500);
            }
            
            return response()->json(json_decode($result->getBody(), true));
        }
    }
    public function destroy($id)
    {
        $result = $this->client->request('POST', 'https://private-anon-4dd6595d8c-dbil.apiary-mock.com/content/category/delete/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => '500',
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }
        
        return response()->json(json_decode($result->getBody(), true));
    }
}
