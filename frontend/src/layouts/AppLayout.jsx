import { Outlet } from "react-router-dom";
import Header from "../components/Header";

export default function AppLayout() {
  return (
    <div className="min-h-screen flex flex-col">
      <header>
        <Header />
      </header>

      <main className="bg-gray-400 flex-1 overflow-auto">
        <Outlet />
      </main>
    </div>
  );
}
