import { Outlet, useLocation } from "react-router-dom";
import { Header } from "../components/Header";
import { Footer } from "../components/Footer";
import { useEffect } from "react";

const titlesByPath = {
  "/": "Accueil - JPO-Laplateforme_",
  "/profil": "Profil - JPO-Laplateforme_",
  "/evenement": "Événements - JPO-Laplateforme_",
  "/connexion": "Connexion - JPO-Laplateforme_",
  "/inscription": "Inscription - JPO-Laplateforme_",
  "/admindashboard": "Admin - JPO-Laplateforme_",
  "/admindashboard/users": "Admin - Utilisateurs - JPO-Laplateforme_",
  "/admindashboard/jpo": "Admin - JPO - JPO-Laplateforme_",
};

export function AppLayout() {
  const location = useLocation();

  useEffect(() => {
    const title = titlesByPath[location.pathname] || "JPO-Laplateforme_";
    document.title = title;
  }, [location.pathname]);

  return (
    <div className="min-h-screen flex flex-col">
      <header>
        <Header />
      </header>

      <main className="bg-white mt-[64px] flex-1 overflow-auto">
        <Outlet />
      </main>

      <footer>
        <Footer />
      </footer>
    </div>
  );
}
