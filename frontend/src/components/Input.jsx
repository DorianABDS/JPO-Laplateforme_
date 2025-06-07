export function Input({ value, onChange, className = "", placeholder = "Name", name, type = "text" }) {
  const baseClass =
    "bg-white text-gray-500 border-gray-300 focus:ring-[#0062FF] focus:border-[#0062FF] w-full pl-4 p-2 rounded-full border focus:outline-none focus:ring-1 transition duration-300";

  return (
    <input
      type={type}
      name={name}
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      className={`${baseClass} ${className}`}
    />
  );
}
