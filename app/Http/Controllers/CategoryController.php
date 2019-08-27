<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CategoryController extends Controller
{
    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    private function checkduplicate($name)
    {
        $result = $this->client->request('POST', $this->endpoint.'content/category/search', [
            'form_params' => [
                'name' => $name
            ]
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

        $result = $this->client->request('GET', $this->endpoint.'content/category');

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        return response()->json(json_decode($result->getBody(), true));

    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|max:255|regex:/[a-zA-Z0-9\s]+/',
            'description' => 'required|max:255|regex:/[a-zA-Z0-9\s]+/'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $check_duplicate = self::checkduplicate($request->name);

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => $check_duplicate['response'],
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
            $result = $this->client->request('POST', $this->endpoint.'content/category/store', [
                'form_params' => [
                    'name' => $request->name,
                    'description' => $request->description
                ]
            ]);
            
            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }

            return response()->json(json_decode($result->getBody(), true));
        }
    }
    
    public function show($id)
    {
        $result = $this->client->request('GET', $this->endpoint.'content/category/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        return response()->json(json_decode($result->getBody(), true));
    }

    public function search()
    {
        $result = $this->client->request('POST', $this->endpoint.'content/category/search', [
            'form_params' => [
                'name' => $_GET['name']
            ]
        ]);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        $search_category = json_decode($result->getBody(), true);

        return response()->json($search_category, $result->getStatusCode());  
 
              
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'name' => 'required|max:255|regex:/[a-zA-Z0-9\s]+/',
            'description' => 'required|max:255|regex:/[a-zA-Z0-9\s]+/'
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $check_duplicate = self::checkduplicate($request->name);

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => $check_duplicate['response'],
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
            $result = $this->client->request('POST', $this->endpoint.'content/category/update/'.$id, [
                'form_params' => [
                    'name' => $request->name,
                    'description' => $request->description
                ]
            ]);

            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }
            
            return response()->json(json_decode($result->getBody(), true));
        }
    }
    public function destroy($id)
    {
        $result = $this->client->request('POST', $this->endpoint.'content/category/delete/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }
        
        return response()->json(json_decode($result->getBody(), true));
    }
}
