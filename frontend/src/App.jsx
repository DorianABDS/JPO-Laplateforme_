import { createBrowserRouter, RouterProvider, Navigate } from "react-router-dom";
import { AppLayout } from "./layouts/AppLayout.jsx";
import { Home } from "./pages/Home.jsx";
import { Profile } from "./pages/Profile.jsx";
import { Events } from "./pages/Events.jsx";
import { AdminLayout } from "./layouts/AdminLayout.jsx";
import { AdminDashboard } from "./pages/admin/AdminDashboard.jsx";
import { AdminUsers } from "./pages/admin/AdminUsers.jsx";
import { AdminJPO } from "./pages/admin/AdminJPO.jsx";
import { Login } from "./pages/Login.jsx";
import { Register } from "./pages/Register.jsx";
import { NotFound } from "./pages/NotFound.jsx";

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


export function App() {
  return <RouterProvider router={router} />;
}
