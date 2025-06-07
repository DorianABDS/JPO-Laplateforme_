// React Router
import { RouterProvider, createBrowserRouter } from "react-router-dom";

// Contexte et composants de transition
import { TransitionProvider } from './context/TransitionContext.jsx';
import TransitionComponent from "./components/Transition.jsx";

// Layouts
import { AppLayout } from "./layouts/AppLayout.jsx";
import { AdminLayout } from "./layouts/AdminLayout.jsx";

// Pages utilisateurs
import { Home } from "./pages/Home.jsx";
import { Profile } from "./pages/Profile.jsx";
import { Events } from "./pages/Events.jsx";
import { Login } from "./pages/Login.jsx";
import { Register } from "./pages/Register.jsx";
import { NotFound } from "./pages/NotFound.jsx";

// Pages admin
import { AdminDashboard } from "./pages/admin/AdminDashboard.jsx";
import { AdminUsers } from "./pages/admin/AdminUsers.jsx";
import { AdminJPO } from "./pages/admin/AdminJPO.jsx";

// Configuration des routes principales
const router = createBrowserRouter([
  {
    path: "/", // Routes publiques
    element: <AppLayout />,
    children: [
      { path: "", element: <TransitionComponent><Home /></TransitionComponent> },
      { path: "profil", element: <TransitionComponent><Profile /></TransitionComponent> },
      { path: "evenement", element: <TransitionComponent><Events /></TransitionComponent> },
      { path: "connexion", element: <TransitionComponent><Login /></TransitionComponent> },
      { path: "inscription", element: <TransitionComponent><Register /></TransitionComponent> },
      { path: "*", element: <TransitionComponent><NotFound /></TransitionComponent> }, // error 404
    ],
  },
  {
    path: "/admindashboard", // Routes accessibles aux administrateurs
    element: <AdminLayout />,
    children: [
      { index: true, element: <TransitionComponent><AdminDashboard /></TransitionComponent> },
      { path: "users", element: <TransitionComponent><AdminUsers /></TransitionComponent> },
      { path: "jpo", element: <TransitionComponent><AdminJPO /></TransitionComponent> },
    ],
  },
]);

// App principale avec Provider pour le contexte de transition
export function App() {
  return (
    <TransitionProvider>
      <RouterProvider router={router} />
    </TransitionProvider>
  );
}
