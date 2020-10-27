<?php

return [

    // auth server host
    'appid_auth_server_host'=> env('APPID_AUTH_SERVER_HOST','https://us-south.appid.cloud.ibm.com'),

    // app id tenant id
    'appid_tenant_id'=> env('APPID_TENANT_ID','ff005170-4ee5-44a9-a8d0-17e1bbf9f52b'),

    // app id client id
    'appid_client_id'=> env('APPID_CLIENT_ID','a7c45af3-2191-4731-bd94-abb485ca1c0c'),

    // app id client secret
    'appid_secret'=> env('APPID_SECRET','OGFkMGI0NDAtNTIxNi00ZmFhLWE3YzQtNDcwNDRlMjNjM2Qz'),

    // app id token
    'appid_access_token'=> '',

    'appid_id_token'=> '',

    'appid_refresh_token'=> '',

    //ibm cloud iam server host
    'ibmcloud_iam_server_host'=> env('IBMCLOUD_IAM_SERVER_HOST','https://iam.cloud.ibm.com'),

    //ibm cloud iam api key
    'ibmcloud_api_key' => env('IBMCLOUD_API_KEY','aRXbqGVEkvXJ9PCPF8HuNmXW-aywKok6bkIY736vWwrq'),

    // this token should get from auth
    'ibmcloud_iam_token' => '',


    'appid_profiles_server_host' => env('APPID_PROFILES_SERVER_HOST','https://us-south.appid.cloud.ibm.com'),

    'appid_management_server_host' => env('APPID_MANAGEMENT_SERVER_HOST','https://us-south.appid.cloud.ibm.com'),

    'appid_redirect_uri' => env('APPID_REDIRECT_URI','http://localhost/auth/user')
];
