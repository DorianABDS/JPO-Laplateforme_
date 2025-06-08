import { Link } from "react-router-dom";
import { Instagram, Facebook, Linkedin, Twitter, Youtube } from 'lucide-react';

export function Footer() {
  return (
    <footer className="bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] text-white rounded p-10 flex flex-col items-center gap-8">
      
      {/* Menu de navigation avec liens internes */}
      <nav className="flex flex-col md:flex-row flex-wrap justify-center items-center gap-4 md:gap-6 text-center">
        <Link to="/" className="link hover:text-gray-300">Accueil</Link>
        <Link to="/profil" className="link hover:text-gray-300">Profil</Link>
        <Link to="/evenements" className="link hover:text-gray-300">Événement</Link>
        <Link to="/inscription" className="link hover:text-gray-300">Inscription</Link>
        <Link to="/connexion" className="link hover:text-gray-300">Connexion</Link>
      </nav>

      {/* Icônes réseaux sociaux avec liens externes */}
      <div className="flex gap-6 justify-center flex-wrap">
        <a href="https://www.facebook.com/LaPlateformeIO" aria-label="Facebook" className="hover:text-gray-300" target="_blank" rel="noopener noreferrer">
          <Facebook />
        </a>
        <a href="https://www.instagram.com/LaPlateformeIO/" aria-label="Instagram" className="hover:text-gray-300" target="_blank" rel="noopener noreferrer">
          <Instagram />
        </a>
        <a href="https://www.linkedin.com/school/laplateformeio/" aria-label="Linkedin" className="hover:text-gray-300" target="_blank" rel="noopener noreferrer">
          <Linkedin />
        </a>
        <a href="https://twitter.com/LaPlateformeIO" aria-label="Twitter" className="hover:text-gray-300" target="_blank" rel="noopener noreferrer">
          <Twitter />
        </a>
        <a href="https://www.youtube.com/c/LaPlateformeIO" aria-label="Youtube" className="hover:text-gray-300" target="_blank" rel="noopener noreferrer">
          <Youtube />
        </a>
      </div>

      {/* Mention copyright */}
      <aside className="text-center text-sm text-gray-200">
        <p>© {new Date().getFullYear()} - Tous droits réservés · Web Workers</p>
      </aside>
    </footer>
  );
}
