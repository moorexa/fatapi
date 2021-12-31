<?php

return array (
  '09b591d3f2154af6578da226749ffc74' => 
  array (
    'query' => 'SELECT * FROM user_groups WHERE usergroupid = :usergroupid or group_name = :group_name ',
    'bind' => 
    array (
      'usergroupid' => 'Accountant',
      'group_name' => '',
    ),
  ),
  'db2aa6f5ceff18e772678d7348f792ea' => 
  array (
    'query' => 'SELECT unquieid FROM routing_center {where}',
    'bind' => 
    array (
    ),
  ),
  'ec6f6aba44524964538506feb4865a84' => 
  array (
    'query' => 'SELECT * FROM routing_center WHERE authentication_token = :authentication_token ',
    'bind' => 
    array (
      'authentication_token' => '8586fb2e033f33de1a2f84',
    ),
  ),
  '8f1d399a15b5c8837d068b970f5848ca' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You added a user group',
      'table_name0' => 'user_groups',
      'method0' => 'insert',
      'tableid0' => '9',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
    ),
  ),
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
  '3b8d6d4849f4f63f09c270ab968a21c2' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You updated a user group from \\"Accountant\\" to \\"Accountant\\"',
      'table_name0' => 'user_groups',
      'method0' => 'update',
      'tableid0' => '9',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
    ),
  ),
  'bdb0538f07699d017c2d4eedda89d07b' => 
  array (
    'query' => 'SELECT * FROM ticket_category WHERE ticketcategoryid = :ticketcategoryid or ticket_category = :ticket_category ',
    'bind' => 
    array (
      'ticketcategoryid' => 'Payment Issue',
      'ticket_category' => '',
    ),
  ),
  '1630b2e8e9b55be1718133e22d2f726c' => 
  array (
    'query' => 'INSERT INTO ticket_category (ticket_category,createdby) VALUES (:ticket_category0,:createdby0)',
    'bind' => 
    array (
      'ticket_category0' => 'Payment Issue',
      'createdby0' => 17,
    ),
  ),
  '55881a45ab2ece5259fc515da8d3076e' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You created a new ticket category \\"Payment Issue\\"',
      'table_name0' => 'ticket_category',
      'method0' => 'insert',
      'tableid0' => '1',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
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
  '7b62f1c6bf715a4eb48d6b4741c2c45c' => 
  array (
    'query' => 'SELECT * FROM ticket_category WHERE ticketcategoryid = :ticketcategoryid or ticket_category = :ticket_category ',
    'bind' => 
    array (
      'ticketcategoryid' => 'Payment Issue 1',
      'ticket_category' => '',
    ),
  ),
  'a1acf5a3fec3043de66d87c94d71630d' => 
  array (
    'query' => 'INSERT INTO ticket_category (ticket_category,createdby) VALUES (:ticket_category0,:createdby0)',
    'bind' => 
    array (
      'ticket_category0' => 'Payment Issue 1',
      'createdby0' => 17,
    ),
  ),
  'c340b13b554bbfd89d9649a0926cf97b' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You created a new ticket category \\"Payment Issue 1\\"',
      'table_name0' => 'ticket_category',
      'method0' => 'insert',
      'tableid0' => '2',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
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
  'bba9d335af9c4e56042cbd25e023d580' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You updated a ticket category from \\"Payment Issue\\" to \\"Payment issue 2\\"',
      'table_name0' => 'ticket_category',
      'method0' => 'update',
      'tableid0' => '1',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
    ),
  ),
  'd8117d07f30165996a656573a3243780' => 
  array (
    'query' => 'SELECT * FROM professional_body WHERE professionalbodyid = :professionalbodyid or professional_body = :professional_body or professional_body_title = :professional_body_title ',
    'bind' => 
    array (
      'professionalbodyid' => '14',
      'professional_body' => '',
      'professional_body_title' => '',
    ),
  ),
  'cd66a39c2d4780ebcf8b1557e906369d' => 
  array (
    'query' => 'SELECT * FROM professional_body WHERE professionalbodyid = :professionalbodyid or professional_body = :professional_body or professional_body_title = :professional_body_title ',
    'bind' => 
    array (
      'professionalbodyid' => '12',
      'professional_body' => '',
      'professional_body_title' => '',
    ),
  ),
  'a8411716c2928f015734a09b88d09301' => 
  array (
    'query' => 'DELETE FROM professional_body WHERE professionalid = :professionalid ',
    'bind' => 
    array (
      'professionalid' => 12,
    ),
  ),
  '836db356601648dcddfdab4327903a3e' => 
  array (
    'query' => 'DELETE FROM professional_body WHERE professionalbodyid = :professionalbodyid ',
    'bind' => 
    array (
      'professionalbodyid' => 12,
    ),
  ),
  '53f20b9874554495931f51ba119246ac' => 
  array (
    'query' => 'SELECT * FROM years WHERE yearid = :yearid or year = :year0 ',
    'bind' => 
    array (
      'yearid' => '3',
      'year0' => '',
    ),
  ),
  '4cfa2cfd4a2d380c35ab4c834ce24ca7' => 
  array (
    'query' => 'DELETE FROM years WHERE yearid = :yearid ',
    'bind' => 
    array (
      'yearid' => 3,
    ),
  ),
  'e4d1bf05fb9ccdc0c65318634d96f7d4' => 
  array (
    'query' => 'SELECT * FROM ticket_category WHERE ticketcategoryid = :ticketcategoryid or ticket_category = :ticket_category ',
    'bind' => 
    array (
      'ticketcategoryid' => '3',
      'ticket_category' => '',
    ),
  ),
  '2fee1cf5a209c6a4ef21d846ae05ded8' => 
  array (
    'query' => 'DELETE FROM ticket_category WHERE ticketcategoryid = :ticketcategoryid ',
    'bind' => 
    array (
      'ticketcategoryid' => 1,
    ),
  ),
  'c8896a9cf4b1c630d2bcc7d1eb0a7dfa' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You deleted a ticket category \\"Payment issue 2\\"',
      'table_name0' => 'ticket_category',
      'method0' => 'delete',
      'tableid0' => '1',
      'logged_by0' => '',
    ),
  ),
  '7284efaa9acf260a9dd624c754c73c2d' => 
  array (
    'query' => 'SELECT * FROM ticket_department WHERE ticketdepartmentid = :ticketdepartmentid or ticket_department = :ticket_department ',
    'bind' => 
    array (
      'ticketdepartmentid' => 'officer',
      'ticket_department' => '',
    ),
  ),
  'a1c6613581d00871d5c570063f7a722d' => 
  array (
    'query' => 'INSERT INTO ticket_department (ticket_department,usergroupid,createdby) VALUES (:ticket_department0,:usergroupid0,:createdby0)',
    'bind' => 
    array (
      'ticket_department0' => 'officer',
      'usergroupid0' => '2',
      'createdby0' => 17,
    ),
  ),
  '004956f30117f7a2b1573a78f9c9da2c' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You created a new ticket department \\"officer\\"',
      'table_name0' => 'ticket_department',
      'method0' => 'insert',
      'tableid0' => '1',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
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
  '39973c4efc8f18e4f37d9d7e830856f4' => 
  array (
    'query' => 'SELECT * FROM ticket_department WHERE ticketdepartmentid = :ticketdepartmentid or ticket_department = :ticket_department ',
    'bind' => 
    array (
      'ticketdepartmentid' => 'officer 2',
      'ticket_department' => '',
    ),
  ),
  'e3ba650f4f0548f4a9c4cc82a34fac23' => 
  array (
    'query' => 'INSERT INTO ticket_department (ticket_department,usergroupid,createdby) VALUES (:ticket_department0,:usergroupid0,:createdby0)',
    'bind' => 
    array (
      'ticket_department0' => 'officer 2',
      'usergroupid0' => '2',
      'createdby0' => 17,
    ),
  ),
  '8114541051772c97c88bf01d318b37a9' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You created a new ticket department \\"officer 2\\"',
      'table_name0' => 'ticket_department',
      'method0' => 'insert',
      'tableid0' => '2',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
    ),
  ),
  'be2b2b2efd34aa7deec44feef226049a' => 
  array (
    'query' => 'SELECT * FROM ticket_department WHERE ticketdepartmentid = :ticketdepartmentid or ticket_department = :ticket_department ',
    'bind' => 
    array (
      'ticketdepartmentid' => '2',
      'ticket_department' => '',
    ),
  ),
  '72ee188506966dd7c031e2feaf1c3e90' => 
  array (
    'query' => 'DELETE FROM ticket_department WHERE ticketdepartmentid = :ticketdepartmentid ',
    'bind' => 
    array (
      'ticketdepartmentid' => 2,
    ),
  ),
  '3477ac8bb19b4c123c05537c7da304d2' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You deleted a ticket department \\"officer 2\\"',
      'table_name0' => 'ticket_department',
      'method0' => 'delete',
      'tableid0' => '2',
      'logged_by0' => '295a35b3d389cb54c1efad776c690a61',
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
  '3179c0060e6442b69e0bd33ad7613066' => 
  array (
    'query' => 'INSERT INTO activities (activity,table_name,method,tableid,logged_by) VALUES (:activity0,:table_name0,:method0,:tableid0,:logged_by0)',
    'bind' => 
    array (
      'activity0' => 'You updated a ticket department from \\"officer\\" to \\"officer 3\\"',
      'table_name0' => 'ticket_department',
      'method0' => 'update',
      'tableid0' => '1',
      'logged_by0' => '',
    ),
  ),
);
