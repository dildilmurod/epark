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
                <form class="form-inline mt-2 mt-md-0" method="POST" action="/api/search-by-email">
                    @csrf
                <input class="form-control mr-sm-2" type="text" id="email" placeholder="Search by email" aria-label="Search">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                </form>
            </div>
          </div>
      <p class="lead">{{$data['message']}}</p>
     
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
                  @if($data['user'])
                    <tr>
                      <td>{{ $data['user']->id }}</td>
                      <td>{{ $data['user']->name }}</td>
                      <td>{{ $data['user']->email }}</td>
                      <td>{{ $data['user']->phone }}</td>
                      <td>{{ $data['user']->role_id }}</td>
                      <td>{{ $data['user']->created_at }}</td>
                    </tr>
                    @endif
              </tbody>
            </table>
            <a href="/api/user"><button class="btn btn-outline-success">Back</button></a>
          </div>

      
    </div>
            </div>
        </div>
    </body>
</html>
