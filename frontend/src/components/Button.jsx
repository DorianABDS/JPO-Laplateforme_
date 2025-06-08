import { useNavigate } from "react-router-dom";

export function Button({
  to = null,
  label = "Voir les détails",
  className = "",
  type = "button",
  ariaLabel = null,
}) {
  const navigate = useNavigate();

  // Style de base du bouton
  const baseClass =
    "mt-4 bg-white text-[#0062FF] rounded-full font-semibold shadow transition duration-300 ease-in-out hover:bg-gradient-to-br hover:from-[#0062FF] hover:via-[#0052CC] hover:to-[#0041AA] hover:text-white hover:shadow-lg hover:-translate-y-1";

  // Classes responsive pour texte et padding selon taille d'écran
  const sizeClasses = {
    sm: "text-sm px-4 py-2",
    md: "md:text-base md:px-6 md:py-2",
    lg: "lg:text-lg lg:px-5 lg:py-2",
  };

  // Assemblage des classes responsive
  const sizeClass = `${sizeClasses.sm} ${sizeClasses.md} ${sizeClasses.lg}`;

  // Gestion du clic
  const handleClick = () => {
    if (type !== "submit" && to) {
      navigate(to);
    }
  };

  return (
    <button
      type={type}
      onClick={handleClick}
      className={`${baseClass} ${sizeClass} ${className}`}
      aria-label={ariaLabel ?? label}
    >
      {label}
    </button>
  );
}
