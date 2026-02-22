import { createBrowserRouter, Navigate } from "react-router-dom";
import Login from "./views/login";
import Register from "./views/register";
import Users from "./views/users";
import NotFound from "./views/NotFound";
import DefaultLayout from "./components/DefaultLayout";
import ClientLayout from "./components/ClientLayout";
import Dashboard from "./views/Dashboard";

const router = createBrowserRouter([
    {
        path: "/",
        element:<DefaultLayout/>,
        children:[
            {
                path:"/",
                element: <Navigate to = "/users"/>
            },
            {
                path: "/dashboard",
                element: <Dashboard/>
            },
            {
                path: "/users",
                element: <Users/>
            },
        ]
    },
    {
        path: "/",
        element:<ClientLayout/>,
        children:
        [
            {
                path:"/login",
                element: <Login/>
            },
            {
                path: "/register",
                element: <Register/>
            },
            
        ]
    },
   
    {
        path:"*",
        element: <NotFound/>
    }
]);
export default router;