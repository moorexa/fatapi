<?php

return array (
  '0c1371eff2eb70b5c23c0d3dab6f9b58' => 
  array (
    'query' => 'SELECT * FROM professional_body WHERE professionalbodyid = :professionalbodyid or professional_body = :professional_body or professional_body_title = :professional_body_title ',
    'bind' => 
    array (
      'professionalbodyid' => '13',
      'professional_body' => '',
      'professional_body_title' => '',
    ),
  ),
  '1860270cd242c87683ebd90ecc989e79' => 
  array (
    'query' => 'SELECT * FROM request_authorization WHERE authorization_code = :authorization_code ',
    'bind' => 
    array (
      'authorization_code' => '36838374748493633474',
    ),
  ),
  '7b620f2fc3bc05bccd446e6b3b07be47' => 
  array (
    'query' => 'UPDATE professional_body SET professional_body = :professional_body , professional_body_title = :professional_body_title  {where}',
    'bind' => 
    array (
      'professional_body' => 'Martins & PaulIz',
      'professional_body_title' => 'MAP',
    ),
  ),
  '9e2605abd5037b21f5b9e0cd50434aa0' => 
  array (
    'query' => 'UPDATE professional_body SET professional_body = :professional_body , professional_body_title = :professional_body_title  WHERE professionalbodyid = :professionalbodyid ',
    'bind' => 
    array (
      'professional_body' => 'Martins & PaulIz',
      'professional_body_title' => 'MAP',
      'professionalbodyid' => 13,
    ),
  ),
  'd1d1ca045ff2b3c6198833b7c4003ca9' => 
  array (
    'query' => 'SELECT * FROM users WHERE userid = :userid or username = :username or email = :email ',
    'bind' => 
    array (
      'userid' => '17',
      'username' => '',
      'email' => '',
    ),
  ),
  'e0fc04a04dfb1d97775bccbb3ecb2ed8' => 
  array (
    'query' => 'SELECT * FROM user_groups WHERE usergroupid = :usergroupid ',
    'bind' => 
    array (
      'usergroupid' => '1',
    ),
  ),
  '2d4e48caee49e87a35b5cc73f8c3dc37' => 
  array (
    'query' => 'UPDATE users SET username = :username , email = :email , usergroupid = :usergroupid  WHERE userid = :userid ',
    'bind' => 
    array (
      'username' => 'wekiwork',
      'email' => 'info@test.com',
      'usergroupid' => '1',
      'userid' => 17,
    ),
  ),
  '83e5cbb3fd26ad5febd567a363f98ebf' => 
  array (
    'query' => 'SELECT * FROM user_groups WHERE usergroupid = :usergroupid or group_name = :group_name ',
    'bind' => 
    array (
      'usergroupid' => '9',
      'group_name' => '',
    ),
  ),
  'b5aa1bb9bd3bb51120285c3df5810ca4' => 
  array (
    'query' => 'SELECT * FROM users WHERE userid = :userid or username = :username or email = :email ',
    'bind' => 
    array (
      'userid' => '17',
      'username' => '',
      'email' => '',
    ),
  ),
  '11c040f3fc265940ce7104046d4cda2a' => 
  array (
    'query' => 'UPDATE user_groups SET group_name = :group_name  WHERE usergroupid = :usergroupid ',
    'bind' => 
    array (
      'group_name' => 'Accountant',
      'usergroupid' => 9,
    ),
  ),
  'f7ef231e5ce0e2ef524e29853e2db254' => 
  array (
    'query' => 'SELECT * FROM ticket_category WHERE ticketcategoryid = :ticketcategoryid or ticket_category = :ticket_category ',
    'bind' => 
    array (
      'ticketcategoryid' => '1',
      'ticket_category' => '',
    ),
  ),
  '7a2268e02615ba299e194530a1e6157e' => 
  array (
    'query' => 'SELECT * FROM user_groups WHERE usergroupid = :usergroupid or group_name = :group_name ',
    'bind' => 
    array (
      'usergroupid' => '1',
      'group_name' => '',
    ),
  ),
  '5c6b53c1834dea3d865c00908f2d1d0b' => 
  array (
    'query' => 'UPDATE ticket_category SET ticket_category = :ticket_category  WHERE ticketcategoryid = :ticketcategoryid ',
    'bind' => 
    array (
      'ticket_category' => 'Payment issue 2',
      'ticketcategoryid' => 1,
    ),
  ),
  '66227340898cab0f86370c4a4b68b4d4' => 
  array (
    'query' => 'SELECT * FROM ticket_department WHERE ticketdepartmentid = :ticketdepartmentid or ticket_department = :ticket_department ',
    'bind' => 
    array (
      'ticketdepartmentid' => '1',
      'ticket_department' => '',
    ),
  ),
  '5bdfbe0fdc9df60e443281c9b505ba61' => 
  array (
    'query' => 'SELECT * FROM user_groups WHERE usergroupid = :usergroupid or group_name = :group_name ',
    'bind' => 
    array (
      'usergroupid' => '2',
      'group_name' => '',
    ),
  ),
  '33cb32b1c546bd772c15861ce22c31ab' => 
  array (
    'query' => 'SELECT * FROM users WHERE userid = :userid or username = :username or email = :email ',
    'bind' => 
    array (
      'userid' => '1',
      'username' => '',
      'email' => '',
    ),
  ),
  '989673849746ffa40451bd29b0fe008c' => 
  array (
    'query' => 'UPDATE ticket_department SET ticket_department = :ticket_department  WHERE ticketdepartmentid = :ticketdepartmentid ',
    'bind' => 
    array (
      'ticket_department' => 'officer 3',
      'ticketdepartmentid' => 1,
    ),
  ),
);
