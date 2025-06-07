import { RouterProvider } from "react-router-dom";
import { createBrowserRouter } from "react-router-dom";
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
import { TransitionProvider } from './context/TransitionContext.jsx';
import TransitionComponent from "./components/Transition.jsx";

const router = createBrowserRouter([
  {
    path: "/",
    element: <AppLayout />,
    children: [
      { path: "", element: <TransitionComponent><Home /></TransitionComponent> },
      { path: "profil", element: <TransitionComponent><Profile /></TransitionComponent> },
      { path: "evenement", element: <TransitionComponent><Events /></TransitionComponent> },
      { path: "connexion", element: <TransitionComponent><Login /></TransitionComponent> },
      { path: "inscription", element: <TransitionComponent><Register /></TransitionComponent> },
      { path: "*", element: <TransitionComponent><NotFound /></TransitionComponent> },
    ],
  },
  {
    path: "/admindashboard",
    element: <AdminLayout />,
    children: [
      { index: true, element: <TransitionComponent><AdminDashboard /></TransitionComponent> },
      { path: "users", element: <TransitionComponent><AdminUsers /></TransitionComponent> },
      { path: "jpo", element: <TransitionComponent><AdminJPO /></TransitionComponent> },
    ],
  },
]);

export function App() {
  return (
    <TransitionProvider>
      <RouterProvider router={router} />
    </TransitionProvider>
  );
}
