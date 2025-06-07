import { Link, Outlet } from "react-router-dom";
import { House, User, DoorOpen, CornerDownLeft } from "lucide-react";

export function AdminLayout() {
  return (
    <div className="flex min-h-screen">
      {/* Sidebar de navigation admin */}
      <aside className="w-64 bg-[#0062FF] text-white p-6 flex flex-col gap-4">
        <h2 className="text-2xl font-bold mb-6">Admin</h2>

        <ul>
          <li className="mb-4">
            {/* Lien vers le dashboard admin */}
            <Link to="/admindashboard" className="flex gap-3 hover:text-gray-400">
              <House /> Dashboard
            </Link>
          </li>

          {/* Sous-menu */}
          <div className="ml-3 border-l-2 border-white pl-3 space-y-4">
            <li>
              {/* Lien vers la gestion des utilisateurs */}
              <Link to="/admindashboard/users" className="flex gap-3 hover:text-gray-400">
                <User /> Utilisateurs
              </Link>
            </li>

            <li>
              {/* Lien vers la gestion des JPO */}
              <Link to="/admindashboard/jpo" className="flex gap-3 hover:text-gray-400">
                <DoorOpen /> JPO
              </Link>
            </li>
          </div>
        </ul>

        {/* Lien pour retourner au site principal */}
        <Link
          to="/"
          className="flex gap-3 mt-auto text-sm text-gray-300 hover:text-white"
        >
          <CornerDownLeft /> Retour au site
        </Link>
      </aside>

      {/* Zone de contenu principale */}
      <main className="flex-1 p-8 bg-white overflow-auto">
        <Outlet />
      </main>
    </div>
  );
}
