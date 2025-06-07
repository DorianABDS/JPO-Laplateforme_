import { createBrowserRouter, RouterProvider, Navigate } from "react-router-dom";
import AppLayout from "./layouts/AppLayout";
import { Home } from "./pages/Home";
import { Profile } from "./pages/Profile";
import Events from "./pages/Events";
import AdminLayout from "./layouts/AdminLayout";
import AdminDashboard from "./pages/admin/AdminDashboard";
import AdminUsers from "./pages/admin/AdminUsers";
import AdminJPO from "./pages/admin/AdminJPO";
import Login from "./pages/Login";
import Register from "./pages/Register";
import NotFound from "./pages/NotFound";

const router = createBrowserRouter([
  {
    path: "/",
    element: <AppLayout />,
    children: [
      { path: "", element: <Home /> },
      { path: "profil", element: <Profile /> },
      { path: "evenement", element: <Events /> },
      { path: "connexion", element: <Login /> },
      { path: "inscription", element: <Register /> },
      { path: "*", element: <NotFound /> },
    ],
  },

  {
    path: "/admindashboard",
    element: <AdminLayout />,
    children: [
      { index: true, element: <AdminDashboard /> },
      { path: "users", element: <AdminUsers /> },
      { path: "jpo", element: <AdminJPO /> },
    ],
  },
]);


export default function App() {
  return <RouterProvider router={router} />;
}
