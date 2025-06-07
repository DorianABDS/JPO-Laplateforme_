import { useNavigate } from "react-router-dom";

export function Button({
  to = null,
  label = "Voir les détails",
  className = "",
  type = "button",
}) {
  const navigate = useNavigate();

  const baseClass =
    "mt-4 bg-white text-[#0062FF] hover:bg-gray-300 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition duration-200 shadow self-start";

  const handleClick = () => {
    if (type !== "submit" && to) {
      navigate(to);
    }
  };

  return (
    <button type={type} onClick={handleClick} className={`${baseClass} ${className}`}>
      {label}
    </button>
  );
}
