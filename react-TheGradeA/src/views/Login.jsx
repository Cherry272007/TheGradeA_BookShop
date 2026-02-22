import React from 'react'
import { Link } from 'react-router-dom';

function Login() {
    const onSubmit = (e) => {  
        e.preventDefault();
    }
  return (
    <div className='login-signup-form animated fadeInDown'>
      <div className="form">
        <form action="" onSubmit={onSubmit}>
            <h1 className='title'>Login</h1>
            <input type="email" name="email" placeholder='email' id="email" />
            <input type="password" name="password" placeholder='password' id="password" />
            <button className='btn btn-block' type='submit'>Login</button>

            <p className='message'>
                Don't have an account? <Link to="/register">Register</Link>
            </p>
        </form>
      </div>
    </div>
  )
}

export default Login
