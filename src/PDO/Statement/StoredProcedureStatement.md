# How to
Here is an sample of how i used. First i show how i used the normal PDO, the
sample is how i use the Slim\PDO.  
Note that this is only used with FireBirdSQL

## Default PDO
get the params
```php
$ownerid    = 0;
$initial    = filter_var($args['initial'], FILTER_SANITIZE_STRING);
$fullname   = filter_var($args['fullname'], FILTER_SANITIZE_STRING);
$surname    = filter_var($args['surname'], FILTER_SANITIZE_STRING);
$givenname  = filter_var($args['givenname'], FILTER_SANITIZE_STRING);
$midname    = filter_var($args['midname'], FILTER_SANITIZE_STRING);
$title      = filter_var($args['title'], FILTER_SANITIZE_STRING);
$notes      = filter_var($args['notes'], FILTER_SANITIZE_STRING);
```

prepare the statements
```php
$stmt = $this->pdo->prepare("execute procedure insert_person_test(?,?,?,?,?,?,?,?);");
```

Bind the params or values
```php
$stmt->bindParam(1, $ownerid, PDO::PARAM_INT);
$stmt->bindParam(2, $initial);
$stmt->bindParam(3, $fullname);
$stmt->bindParam(4, $surname);
$stmt->bindParam(5, $givenname);
$stmt->bindParam(6, $midname);
$stmt->bindParam(7, $title);
$stmt->bindParam(8, $notes);
```

Execute the statement and fetch the data;
```php
$stmt->execute();
$data = $stmt->fetchall();
```

## Slim\PDO
get the params/values
```php
$lvalues[] = [0 => ['data_type' => PDO::PARAM_INT]];
$lvalues[] = [filter_var($args['initial'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['fullname'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['surname'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['givenname'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['midname'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['title'], FILTER_SANITIZE_STRING) => []];
$lvalues[] = [filter_var($args['notes'], FILTER_SANITIZE_STRING) => []];
```
build and execute the statement and fetch the data;
```php
$stmt = $this->pdo->execSP('execute procedure')
                    ->storedprocedureName('insert_person_test')
                    ->values($lvalues)
                    ->execute();
$data = $stmt->fetchall();
```
