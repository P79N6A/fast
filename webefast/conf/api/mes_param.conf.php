<?php

return array(
    'login' => array(
        'ApiType' => 'Platform.Security.AuthenticationController, Platform',
        'Parameters' => array(
        ),
        'Method' => 'Login',
        'Context' => array(
            "Ticket" => null,
        ),
    ),
    'record_return' => array(
        'ApiType' => 'RiFengWMS.eFastErps.eFastInterfaceController, eFastInterface',
        'Parameters' => array(
        ),
        'Method' => 'PushRma',
        'Context' => array(
            "InvOrgId" => "8010",
            "Ticket" => NULL,
        ),
    ),
    'record' => array(
        'ApiType' => 'RiFengWMS.eFastErps.eFastInterfaceController, eFastInterface',
        'Parameters' => array(
        ),
        'Method' => 'PushSalesIssue',
        'Context' => array(
            "InvOrgId" => "8010",
            "Ticket" => NULL,
        ),
    ),
    'cancel_return' => array(
        'ApiType' => 'RiFengWMS.eFastErps.eFastInterfaceController, eFastInterface',
        
        'Parameters' => array(            
        ),
        'Method' => 'CancelRmaBill',
        'Context' => array(
            "InvOrgId" => "8010",
            "Ticket" => NULL,
        ),
        
    ),
);
