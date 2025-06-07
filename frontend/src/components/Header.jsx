import { useState } from "react";
import { Link } from "react-router-dom";
import { Menu, X } from "lucide-react"; 
import logoLaplateforme from "../assets/img/Logo_Plateforme_bleu.svg";

export function Header() {
  // État pour gérer l'ouverture/fermeture du menu mobile
  const [isOpen, setIsOpen] = useState(false);

  return (
    <nav className="bg-white shadow-md w-screen py-4 px-6 md:px-20 flex justify-between items-center h-16 shrink-0 fixed z-50">
      {/* Logo cliquable qui redirige vers la page d'accueil */}
      <Link to="/">
        <img src={logoLaplateforme} alt="Logo La Plateforme" className="h-24 p-0" />
      </Link>

      {/* Navigation desktop visible à partir de md */}
      <div className="hidden md:flex gap-4 items-center">
        <Link
          to=""
          className="font-Poppins font-semibold text-md text-[#0062FF] hover:text-[#353535]"
        >
          Accueil
        </Link>
        <Link
          to="/profil"
          className="font-Poppins font-semibold text-md text-[#0062FF] hover:text-[#353535]"
        >
          Profil
        </Link>
        <Link
          to="/events"
          className="font-Poppins font-semibold text-md text-[#0062FF] hover:text-[#353535]"
        >
          Événements
        </Link>
        <Link
          to="/admindashboard"
          className="font-Poppins font-semibold text-md text-[#0062FF] hover:text-[#353535]"
        >
          Admin
        </Link>

        {/* Bouton Inscription */}
        <Link
          to="/inscription"
          className="font-Poppins font-semibold text-md bg-[#0062FF] text-white px-5 py-2 ml-5 rounded-full hover:bg-[#0051cc] transition-colors"
        >
          Inscription
        </Link>

        {/* Bouton Connexion */}
        <Link
          to="/connexion"
          className="font-Poppins font-semibold text-md border border-[#0062FF] text-[#0062FF] px-5 py-2 rounded-full hover:bg-[#0062FF] hover:text-white transition-colors"
        >
          Connexion
        </Link>
      </div>

      {/* Bouton toggle menu mobile visible uniquement en dessous de md */}
      <button
        className="md:hidden bg-white text-blue-500"
        onClick={() => setIsOpen(!isOpen)}
        aria-label="Menu"
      >
        {/* Icone menu ou croix selon l'état */}
        {isOpen ? <X size={30} /> : <Menu size={30} />}
      </button>

      {/* Menu mobile affiché uniquement si isOpen true */}
      {isOpen && (
        <div className="absolute top-full left-0 w-full bg-white text-[#0062FF] font-semibold shadow-md flex flex-col items-center gap-4 py-4 md:hidden z-50">
          {/* Liens de navigation mobile avec fermeture du menu au clic */}
          <Link to="/" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Accueil</Link>
          <Link to="/profile" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Profil</Link>
          <Link to="/inscription" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Inscription</Link>
          <Link to="/login" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Connexion</Link>
        </div>
      )}
    </nav>
  );
}
