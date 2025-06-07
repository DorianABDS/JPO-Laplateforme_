// Bouton qui navigue vers une route spécifiée au clic
import { useNavigate } from "react-router-dom";

export function HeroButton({
  to = "/events",
  label = "Explorer nos JPO",
  className = ""
}) {
  const navigate = useNavigate();

  const baseClass = "text-xl bg-[#0062FF] hover:bg-[#0051cc] shadow sm:text-2xl font-semibold text-white h-14 sm:h-14 w-full sm:w-52 rounded-full transition";

  return (
    <button onClick={() => navigate(to)} className={`${baseClass} ${className}`}>
      {label}
    </button>
  );
}
