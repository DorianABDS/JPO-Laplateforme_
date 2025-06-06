import { Link } from "react-router-dom";
import logoLaplateforme from "../assets/img/Logo_Plateforme_blanc.svg";

export default function Footer() {
  return (
    <footer className="bg-[#0062FF] shadow-md py-8 px-6 md:px-20">
      <div className="flex flex-col md:flex-row justify-between items-center gap-6 md:gap-0">
        <Link to="/" className="flex justify-center md:justify-start w-full md:w-auto">
          <img src={logoLaplateforme} alt="Logo La Plateforme" className="h-20 md:h-32" />
        </Link>

        <nav className="w-full md:w-auto">
          <ul className="flex flex-col md:flex-row md:gap-8 items-center text-center md:text-left">
            <li>
              <Link
                to=""
                className="font-Poppins font-semibold text-lg text-white hover:text-[#353535] block py-2 md:py-0">
                Accueil
              </Link>
            </li>
            <li>
              <Link
                to="/profile"
                className="font-Poppins font-semibold text-lg text-white hover:text-[#353535] block py-2 md:py-0">
                Profil
              </Link>
            </li>
            <li>
              <Link
                to="/evenements"
                className="font-Poppins font-semibold text-lg text-white hover:text-[#353535] block py-2 md:py-0">
                Événements
              </Link>
            </li>
            <li>
              <Link
                to="/inscription"
                className="font-Poppins font-semibold text-lg text-white hover:text-[#353535] block py-2 md:py-0">
                Inscription
              </Link>
            </li>
            <li>
              <Link
                to="/login"
                className="font-Poppins font-semibold text-lg text-white hover:text-[#353535] block py-2 md:py-0">
                Connexion
              </Link>
            </li>
          </ul>
        </nav>
      </div>

      {/* Copyright centré en dessous */}
      <p className="mt-6 text-center text-gray-300 text-sm font-Poppins">
        © {new Date().getFullYear()} LaPlateforme. Tous droits réservés.
      </p>
    </footer>
  );
}
