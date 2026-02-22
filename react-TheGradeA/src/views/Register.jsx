import React from 'react'
import { Link } from 'react-router-dom'

function Register() {
    const onSubmit = (e) => {
        e.preventDefault()
    }
  return (
    <div className='login-signup-form animated fadeInDown'>
      <div className="form">
        <form action="" onSubmit={onSubmit}>
            <h1 className='title'>Register</h1>
            <input type="text" name="name" placeholder='name' id="name" />
            <input type="email" name="email" placeholder='email' id="email" />
            <input type="password" name="password" placeholder='password' id="password" />
            <input type="password" name="password_confirmation" placeholder='confirm password' id="password_confirmation" />
            <button className='btn btn-block' type='submit'>Register</button>

            <p className='message'>
                Do you have an account? <Link to="/login">Login</Link>
            </p>
        </form>
      </div>
    </div>
  )
}

export default Register
