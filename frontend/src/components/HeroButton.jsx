// Bouton qui navigue vers une route spécifiée au clic
import { useNavigate } from "react-router-dom";

export function HeroButton({
  to = "/events",
  label = "Explorer nos JPO",
  className = ""
}) {
  const navigate = useNavigate();

  const baseClass = "text-xl bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] text-white shadow sm:text-2xl font-semibold h-14 sm:h-14 w-full sm:w-52 rounded-full transition duration-300 ease-in-out hover:bg-none hover:bg-white hover:text-[#0062FF] hover:shadow-lg hover:-translate-y-1";

return (
    <button onClick={() => navigate(to)} className={`${baseClass} ${className}`}>
      {label}
    </button>
  );
}
