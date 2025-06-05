import { useState } from "react";
import { Link } from "react-router-dom";
import { Menu, X } from "lucide-react"; 
import logoLaplateforme from "../assets/img/Logo_Plateforme_bleu.svg";

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <nav className="bg-white shadow-md py-4 px-6 md:px-20 flex justify-between items-center h-16 shrink-0 relative">
      <Link to="/">
        <img src={logoLaplateforme} alt="Logo La Plateforme" className="h-10" />
      </Link>

      {/* Desktop nav */}
      <div className="hidden md:flex gap-4 mx-10">
        <Link to="/" className="font-Poppins font-medium text-lg text-[#0062FF] hover:text-[#353535]">Accueil</Link>
        <Link to="/profile" className="font-Poppins font-medium text-lg text-[#0062FF] hover:text-[#353535]">Profil</Link>
        <Link to="/evenements" className="font-Poppins font-medium text-lg text-[#0062FF] hover:text-[#353535]">Événements</Link>
        <Link to="/inscription" className="font-Poppins font-medium text-lg text-[#0062FF] hover:text-[#353535]">Inscription</Link>
        <Link to="/login" className="font-Poppins font-medium text-lg text-[#0062FF] hover:text-[#353535]">Connexion</Link>
      </div>

      {/* Mobile toggle button */}
      <button
        className="md:hidden bg-white text-blue-500"
        onClick={() => setIsOpen(!isOpen)}
        aria-label="Menu"
      >
        {isOpen ? <X size={30} /> : <Menu size={30} />}
      </button>

      {/* Mobile nav */}
      {isOpen && (
        <div className="absolute top-full left-0 w-full bg-white shadow-md flex flex-col items-center gap-4 py-4 md:hidden z-50">
          <Link to="/" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Accueil</Link>
          <Link to="/profile" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Profil</Link>
          <Link to="/inscription" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Inscription</Link>
          <Link to="/login" className="hover:text-blue-500" onClick={() => setIsOpen(false)}>Connexion</Link>
        </div>
      )}
    </nav>
  );
}
