import { Link } from "react-router-dom";
import logoLaplateforme from "../assets/img/Logo_Plateforme_bleu.svg";

export default function Header() {
  return (
    <nav className="bg-white shadow-md p-4 flex justify-between items-center">
      <Link to="/">
        <img src={logoLaplateforme} alt="Logo La Plateforme" className="h-10" />
      </Link>
      <div className="flex gap-4">
        <Link to="/" className="hover:text-blue-500">Accueil</Link>
        <Link to="/profile" className="hover:text-blue-500">Profil</Link>
        <Link to="/login" className="hover:text-blue-500">Connexion</Link>
      </div>
    </nav>
  );
}
