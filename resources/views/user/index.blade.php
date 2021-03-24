<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>E-commerce</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

        
    </head>
    <body>
        <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h3>User</h3>
            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4">
                <form class="form-inline" method="POST" action="/api/search-by-email">
                    @csrf
                <input class="form-control" type="text" placeholder="Search by email" name="email">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                </form>
            </div>
          </div>
      <p class="lead">All registred users</p>
     
      <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>E-mail</th>
                  <th>Phone</th>
                  <th>Role</th>
                  <th>Registered date</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $user)
                    <tr>
                      <td>{{ $user->id }}</td>
                      <td>{{ $user->name }}</td>
                      <td>{{ $user->email }}</td>
                      <td>{{ $user->phone }}</td>
                      <td>{{ $user->role_id }}</td>
                      <td>{{ $user->created_at }}</td>
                    </tr>
                @endforeach
                
              </tbody>
            </table>
            {{$users->links()}}
          </div>

      
    </div>
            </div>
        </div>
    </body>
</html>
