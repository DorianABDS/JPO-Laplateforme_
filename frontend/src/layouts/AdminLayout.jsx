import { Link, Outlet } from "react-router-dom";
import { House, User, DoorOpen, CornerDownLeft } from "lucide-react";

export default function AdminLayout() {
  return (
    <div className="flex min-h-screen">
      {/* Sidebar */}
      <aside className="w-64 bg-[#0062FF] text-white p-6 flex flex-col gap-4">
        <h2 className="text-2xl font-bold mb-6">Admin</h2>
        <Link to="/admindashboard" className="flex gap-3 hover:text-gray-400">
          <House /> Dashboard
        </Link>
        <Link to="/admindashboard/users" className="flex ml-3 gap-3 hover:text-gray-400">
          <User /> Utilisateurs
        </Link>
        <Link to="/admindashboard/jpo" className="flex ml-3 gap-3 hover:text-gray-400">
          <DoorOpen /> JPO
        </Link>
        <Link to="/" className="flex gap-3 mt-auto text-sm text-gray-300 hover:text-white">
          <CornerDownLeft /> Retour au site
        </Link>
      </aside>

      {/* Contenu principal */}
      <main className="flex-1 p-8 bg-white overflow-auto">
        <Outlet />
      </main>
    </div>
  );
}
