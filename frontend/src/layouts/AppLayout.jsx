import { Outlet, useLocation } from "react-router-dom";
import { Header } from "../components/Header";
import { Footer } from "../components/Footer";
import { useEffect } from "react";

// Définition des titres de page en fonction du chemin
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

// Layout principal utilisateur
export function AppLayout() {
  const location = useLocation();

  // Mise à jour du titre de la page lors du changement de route
  useEffect(() => {
    const title = titlesByPath[location.pathname] || "JPO-Laplateforme_";
    document.title = title;
  }, [location.pathname]);

  return (
    <div className="min-h-screen flex flex-col">
      <header>
        <Header />
      </header>

      {/* Contenu de la page */}
      <main className="bg-white mt-[64px] flex-1 overflow-auto">
        <Outlet />
      </main>

      <footer>
        <Footer />
      </footer>
    </div>
  );
}
