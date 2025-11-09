@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 offset-3">

                <h2 class="text-white m-0">Edit Customer</h2>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-warning mt-2">Back</a>

                <div class="card bg-dark text-white mt-4">
                    <div class="card-body border border-light rounded">
                        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input
                                    type="text"
                                    class="form-control bg-dark text-white @error('name') is-invalid @enderror"
                                    name="name"
                                    value="{{old('name', $customer->name)}}">
                                @error('name')
                                        <div class="invalid-feedback">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    class="form-control bg-dark text-white @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{old('email', $customer->email)}}">
                                @error('email')
                                        <div class="invalid-feedback">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control bg-dark text-white @error('password') is-invalid @enderror"
                                    name="password"
                                    placeholder="Leave blank to keep current password">
                                @error('password')
                                        <div class="invalid-feedback">{{$message}}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-outline-success text-white">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
