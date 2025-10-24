<?php

/** 
 * sso-authen/config/providers/psu.php
 * *  
 */

require_once __DIR__ . '/../config.php';

return [
    'clientID'     => 'mt43k2Hi7a06HyneRUFLWIa9xM1XR8bOeeOvlsj3',
    'clientSecret' => 'z2IHDs4wSKJgDCile9dJO2tkkthyte9gYyUnZyjVQQ649k19edfHYlMyyDv2DGDjTYGunaD4RpeLJlhCjX1FzXphLwHGOf0fPmQy6HwqmJva6lHIf3xlSqWIx3nuvuh3',
    'providerURL'  => 'https://psusso.psu.ac.th/application/o/ptn-oas-shopping/',

    // **สำคัญ:** path ต้องมี /public/ เพิ่มเข้ามาให้ตรงกับโครงสร้างใหม่
    // 'redirectUri'  => 'http://sso-authen.test/public/callback.php',
    'redirectUri'  => $absoluteRedirectUri . '/public/callback.php',

    'scopes'       => ['openid', 'profile', 'email', 'psu_profile'],

    // การแปลงชื่อ Claims จาก PSU SSO ให้เป็นชื่อมาตรฐานที่ Library เราเข้าใจ
    'claim_mapping' => [
        'id'        => 'psu_id',
        'username'  => 'preferred_username',
        'name'      => 'display_name_th',
        'firstName' => 'first_name_th',
        'lastName'  => 'last_name_th',
        'email'     => 'email',
        'department' => 'department_th'
    ]
];
