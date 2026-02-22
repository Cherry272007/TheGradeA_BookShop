
import { RouterProvider } from 'react-router-dom'
import './App.css'
import router from './router'
import { ContextProvider } from './contexts/ContextProvider'

function App() {
  return (
    <>
      <div>
        <ContextProvider>
          <RouterProvider router={router}/>
        </ContextProvider>
      </div>
    </>
  )
}

export default App
