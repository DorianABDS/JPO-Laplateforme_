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
  const baseClass =
    "mt-4 bg-white text-[#0062FF] hover:bg-gray-300 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition duration-200 shadow self-start";

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
