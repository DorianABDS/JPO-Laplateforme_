import { Link } from "react-router-dom";
import { Instagram, Facebook, Linkedin, Twitter, Youtube } from 'lucide-react';

export function Footer() {
  return (
    <footer className="bg-[#0062FF] text-white rounded p-10 flex flex-col items-center gap-8">
      
      {/* Menu liens */}
      <nav className="flex flex-col md:flex-row flex-wrap justify-center items-center gap-4 md:gap-6 text-center">
        <Link to="/" className="link hover:text-gray-300">Accueil</Link>
        <Link to="/profil" className="link hover:text-gray-300">Profil</Link>
        <Link to="/evenements" className="link hover:text-gray-300">Événement</Link>
        <Link to="/inscription" className="link hover:text-gray-300">Inscription</Link>
        <Link to="/connexion" className="link hover:text-gray-300">Connexion</Link>
      </nav>

      {/* Réseaux sociaux */}
      <div className="flex gap-6 justify-center flex-wrap">
        <a href="https://www.facebook.com/LaPlateformeIO" aria-label="Facebook" className="hover:text-gray-300">
          <Facebook />
        </a>
        <a href="https://www.instagram.com/LaPlateformeIO/" aria-label="Instagram" className="hover:text-gray-300">
          <Instagram />
        </a>
        <a href="https://www.linkedin.com/school/laplateformeio/" aria-label="Linkedin" className="hover:text-gray-300">
          <Linkedin />
        </a>
        <a href="https://www.linkedin.com/school/laplateformeio/" aria-label="Twitter" className="hover:text-gray-300">
          <Twitter />
        </a>
        <a href="https://www.youtube.com/c/LaPlateformeIO" aria-label="Youtube" className="hover:text-gray-300">
          <Youtube />
        </a>
      </div>

      {/* Copyright */}
      <aside className="text-center text-sm text-gray-200">
        <p>© {new Date().getFullYear()} - Tous droits réservés · Web Workers</p>
      </aside>
    </footer>
  );
}
