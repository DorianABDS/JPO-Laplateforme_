import { useNavigate } from "react-router-dom";

export default function Button({
  to = "/",
  label = "Voir les details",
  className = ""
}) {
  const navigate = useNavigate();

  const baseClass = "mt-4 bg-white text-[#0062FF] px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition duration-200 shadow self-start"

  return (
    <button onClick={() => navigate(to)} className={`${baseClass} ${className}`}>
      {label}
    </button>
  );
}
