import { useNavigate } from "react-router-dom";

export function Button({
  to = null,
  label = "Voir les détails",
  className = "",
  type = "button",
  ariaLabel = null,
}) {
  const navigate = useNavigate();

  // Classes CSS de base
  const baseClass = "mt-4 bg-white text-[#0062FF] px-6 py-2 rounded-full font-semibold shadow transition duration-300 ease-in-out hover:bg-gradient-to-br hover:from-[#0062FF] hover:via-[#0052CC] hover:to-[#0041AA] hover:text-white hover:shadow-lg hover:-translate-y-1";

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
      className={`${baseClass} ${className}`}
      aria-label={ariaLabel ?? label}
    >
      {label}
    </button>
  );
}
