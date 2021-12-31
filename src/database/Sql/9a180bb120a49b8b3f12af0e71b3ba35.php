<?php

return array (
  'new-db' => 
  array (
    'user_groups' => 
    array (
      '35b2219a0096eca21c0a131162ea84af1a694ee72bb03ca5d7707b8045a4266f0833ae47' => 
      array (
        'query' => 'INSERT INTO user_groups (group_name,createdby) VALUES (:group_name0,:createdby0)',
        'bind' => 
        array (
          'group_name0' => 'Accountant',
          'createdby0' => 17,
        ),
      ),
    ),
    'activities' => 
    array (
      '2a079e8575911b67f3c507730decf0b8ba77cdadd95445856377d988283a95ebe4120f0a' => 
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
      '2a079e8575911b67f3c507730decf0b86379998cc7cef0fe5f8b4b5fb5a4eba1afbef9c5' => 
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
      '2a079e8575911b67f3c507730decf0b86ca23bbf3751b6f63af6a0db9d6a6d070b0383cd' => 
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
      '2a079e8575911b67f3c507730decf0b89cdb93c9c8c9607512114b2d7c6d516124d6d754' => 
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
      '2a079e8575911b67f3c507730decf0b81512caf6237cdd73b5581768970afad7820c5766' => 
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
      '2a079e8575911b67f3c507730decf0b8723365c79eb3c68b9c8f391d9f1ffe0edf8d690a' => 
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
      '2a079e8575911b67f3c507730decf0b8798ffc20659046a2374a3c03dc1244e8c6901c82' => 
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
      '2a079e8575911b67f3c507730decf0b855951c2b217f1ee8d7b09a22a29297ff61f13ea9' => 
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
      '2a079e8575911b67f3c507730decf0b8384d640c8ed45a7d5d7e91135c76527d4d21752e' => 
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
      '2a079e8575911b67f3c507730decf0b825a3e320717c743e32af112903bb64cb005cc86a' => 
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
    ),
  ),
);

?>