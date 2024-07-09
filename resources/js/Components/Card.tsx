import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import DragHandleIcon from "@mui/icons-material/DragHandle";
import EditIcon from "@mui/icons-material/Edit";
import { Box, IconButton } from "@mui/material";
import { useTodoBoard } from "./useTodoBoard";
import { Task } from "@/Apis/tasks";

const Card: React.FC<Task> = (task) => {
  const { id, title } = task;
  const { attributes, listeners, setNodeRef, transform, isDragging, setActivatorNodeRef } =
    useSortable({
      id: id,
    });

  const { setCurrentCard, setDialogOpen, setDialogMode } = useTodoBoard();

  const handleDialogOpen = (task: Task) => {
    setDialogMode("edit");
    setCurrentCard(task);
    setDialogOpen(true);
  };

  const style = {
    width: "100%",
    margin: "2px",
    opacity: 1,
    color: "#333",
    background: "white",
    transform: CSS.Translate.toString(transform),
  };

  return (
    <div ref={setNodeRef} style={style}>
      <div id={id.toString()}>
        <Box
          sx={{
            border: "1px solid gray",
            boxShadow: "0px 0px 16px 2px rgba(0, 0, 0, 0.08)",
            padding: "4px",
            display: "flex",
            alignItems: "center",
            bgcolor: "white",
          }}
        >
          <Box
            ref={setActivatorNodeRef}
            {...attributes}
            {...listeners}
            sx={{
              display: "flex",
              alignItems: "center",
              cursor: isDragging ? "grabbing" : "grab",
            }}
          >
            <DragHandleIcon />
          </Box>
          <Box
            sx={{
              display: "flex",
              flexDirection: "row",
              alignItems: "center",
              justifyContent: "space-between",
              width: "100%",
              ml: 2,
            }}
          >
            <Box sx={{ whiteSpace: "pre-wrap" }}>{title}</Box>
            <IconButton onClick={() => handleDialogOpen(task)}>
              <EditIcon />
            </IconButton>
          </Box>
        </Box>
      </div>
    </div>
  );
};

export default Card;
